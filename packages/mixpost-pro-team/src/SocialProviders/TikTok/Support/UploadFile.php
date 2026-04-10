<?php

namespace Inovector\Mixpost\SocialProviders\TikTok\Support;

use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Concerns\UsesSocialProviderResponse;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\SocialProviders\TikTok\Concerns\ManagesRateLimit;
use Inovector\Mixpost\Support\SocialProviderResponse;
use InvalidArgumentException;
use RuntimeException;

class UploadFile
{
    use ManagesRateLimit;
    use UsesSocialProviderResponse;

    private const MB = 1024 * 1024;

    private const MIN_CHUNK = 5 * self::MB;

    private const MAX_CHUNK = 64 * self::MB;

    private const MAX_FINAL_CHUNK = 128 * self::MB;

    private const MAX_CHUNKS = 1000;

    private SocialProviderResponse $initUploadResponse;

    public function __construct(
        private readonly Media $media,
        private readonly ?array $postInfo,
        private readonly Http $httpClient,
        private readonly string $apiVersion,
        private readonly string $apiUrl,
        private readonly string $accessToken,
    ) {}

    public function upload(): SocialProviderResponse
    {
        $chunkPlan = $this->buildChunkPlan($this->media->size);

        $data['source_info'] = [
            'source' => 'FILE_UPLOAD',
            'video_size' => $this->media->size,
            'chunk_size' => $chunkPlan['chunk_size'],
            'total_chunk_count' => $chunkPlan['total_chunk_count'],
        ];

        // Direct Post
        if ($this->postInfo) {
            $data['post_info'] = $this->postInfo;
        }

        $initUploadResponse = $this->buildResponse(
            $this->httpClient::withToken($this->accessToken)
                ->asJson()
                ->post("$this->apiUrl/$this->apiVersion/post/publish/".($this->postInfo ? 'video' : 'inbox/video').'/init/', $data)
        );

        if ($initUploadResponse->hasError()) {
            return $initUploadResponse;
        }

        $this->initUploadResponse = $initUploadResponse;

        // Upload the whole video as a single chunk
        if ($chunkPlan['total_chunk_count'] === 1) {
            $response = $this->uploadChunk(0, $this->media->size - 1, $this->media->size);

            if ($response->hasError()) {
                return $response;
            }
        }

        // Upload the video in chunks
        if ($chunkPlan['total_chunk_count'] > 1) {
            for ($chunk = 0; $chunk < $chunkPlan['total_chunk_count']; $chunk++) {
                $firstByte = $chunk * $chunkPlan['chunk_size'];

                // For the last chunk, ensure we upload all remaining bytes to the end
                if ($chunk === $chunkPlan['total_chunk_count'] - 1) {
                    $lastByte = $this->media->size - 1;
                } else {
                    $lastByte = ($chunk + 1) * $chunkPlan['chunk_size'] - 1;
                }

                $byteSizeOfChunk = $lastByte - $firstByte + 1;

                $response = $this->uploadChunk((int) $firstByte, (int) $lastByte, (int) $byteSizeOfChunk);

                if ($response->hasError()) {
                    return $response;
                }
            }
        }

        $publishId = $this->initUploadResponse->data['publish_id'];

        return $this->response(SocialProviderResponseStatus::OK, [
            'data' => [
                'publish_id' => $publishId,
            ],
        ]);
    }

    private function uploadChunk($firstByte, $lastByte, $byteSizeOfChunk): SocialProviderResponse
    {
        $contentRange = "bytes $firstByte-$lastByte/{$this->media->size}";
        $readStream = $this->media->readStream();

        $binaryFileData = stream_get_contents($readStream['stream'], $byteSizeOfChunk, $firstByte);

        $response = $this->httpClient::timeout(300)
            ->withHeaders([
                'Content-Range' => $contentRange,
                'Content-Length' => $byteSizeOfChunk,
                'Content-Type' => $this->media->mime_type,
            ])
            ->withBody($binaryFileData, $this->media->mime_type)
            ->put($this->initUploadResponse->data['upload_url']);

        if (is_resource($readStream['stream'])) {
            fclose($readStream['stream']);
        }

        $readStream['temporaryDirectory']?->delete();

        return $this->buildResponse($response);
    }

    /**
     * Determines chunk size and total chunks based on video size, while respecting:
     *
     * @see https://developers.tiktok.com/doc/content-posting-api-media-transfer-guide
     *
     * @throws InvalidArgumentException if videoSize <= 0
     * @throws RuntimeException if constraints cannot be satisfied
     */
    private function buildChunkPlan(int $videoSize): array
    {
        if ($videoSize <= 0) {
            throw new InvalidArgumentException('video_size must be > 0');
        }

        // For videos < 5MB: single chunk equal to file size
        if ($videoSize < self::MIN_CHUNK) {
            return [
                'chunk_size' => $videoSize,
                'total_chunk_count' => 1,
                'last_chunk_size' => $videoSize,
            ];
        }

        // Starting point: 64MB, but limited to [5MB, 64MB]
        $chunkSize = $videoSize;
        $chunkSize = max(self::MIN_CHUNK, min(self::MAX_CHUNK, $chunkSize));

        // Enforce 1000 chunks limit (reduce count by increasing chunk_size if needed)
        $minNeeded = intdiv($videoSize + (self::MAX_CHUNKS - 1), self::MAX_CHUNKS); // ceil
        if ($minNeeded > $chunkSize) {
            $chunkSize = min($minNeeded, self::MAX_CHUNK);
        }

        // If video_size > 64MB, MUST have at least 2 chunks
        if ($videoSize > self::MAX_CHUNK && intdiv($videoSize, $chunkSize) < 2) {
            // Choose largest chunk_size that still produces >= 2 chunks: floor(video_size / 2)
            $half = intdiv($videoSize, 2);
            $chunkSize = max(self::MIN_CHUNK, min(self::MAX_CHUNK, $half));
        }

        // Recalculate total
        $total = intdiv($videoSize, $chunkSize);
        if ($total < 1) {
            $total = 1;
        }

        // Validate upper limit of 1000
        if ($total > self::MAX_CHUNKS) {
            throw new RuntimeException('Cannot satisfy MAX_CHUNKS. Choose a smaller video.');
        }

        // Size of the last chunk (can exceed chunk_size)
        $lastChunkSize = $videoSize - ($total - 1) * $chunkSize;
        if ($lastChunkSize <= 0) {
            // exactly divisible: last chunk has chunk_size
            $lastChunkSize = $chunkSize;
        }

        // Safety validations:
        if ($lastChunkSize > self::MAX_FINAL_CHUNK) {
            // This would mean trailing bytes made the last chunk >128MB -> need to decrease base chunk_size
            throw new RuntimeException('Final chunk would exceed 128MB.');
        }

        return [
            'chunk_size' => $chunkSize,
            'total_chunk_count' => $total,
            'last_chunk_size' => $lastChunkSize,
        ];
    }
}
