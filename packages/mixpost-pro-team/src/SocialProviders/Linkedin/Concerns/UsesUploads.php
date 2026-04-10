<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\PostVersionHelpers;
use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Support\TemporaryFile;
use Inovector\Mixpost\Util;

trait UsesUploads
{
    public function uploadImage(Media|TemporaryFile $media): SocialProviderResponse
    {
        $initializeResponse = $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->withHeaders($this->httpHeadersNext())
                ->post("$this->apiUrl/rest/images?action=initializeUpload", [
                    'initializeUploadRequest' => [
                        'owner' => "urn:li:{$this->author()}:{$this->values['provider_id']}",
                    ],
                ])
        );

        if ($initializeResponse->hasError()) {
            return $initializeResponse;
        }

        $readStream = match (true) {
            $media instanceof Media => $media->readStream(),
            $media instanceof TemporaryFile => ['stream' => $media->readStream(), 'temporaryDirectory' => $media->directory()],
        };

        $uploadResponse = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeaders())
            ->withBody($readStream['stream'], $media->mimeType())
            ->post($initializeResponse->value['uploadUrl']);

        Util::closeAndDeleteStreamResource($readStream);

        return $this->buildResponse($uploadResponse)->useContext([
            'id' => $initializeResponse->value['image'],
        ]);
    }

    public function uploadVideo(Media $media, array $params = []): SocialProviderResponse
    {
        if (isset($params['video_thumbs']) && is_array($params['video_thumbs'])) {
            $thumb = PostVersionHelpers::getThumbForMediaId((int) $media->id, $params['video_thumbs']);
        }

        $initializeResponse = $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->withHeaders($this->httpHeadersNext())
                ->post("$this->apiUrl/rest/videos?action=initializeUpload", [
                    'initializeUploadRequest' => [
                        'owner' => "urn:li:{$this->author()}:{$this->values['provider_id']}",
                        'fileSizeBytes' => $media->size,
                        'uploadThumbnail' => isset($thumb),
                    ],
                ])
        );

        if ($initializeResponse->hasError()) {
            return $initializeResponse;
        }

        $readStream = $media->readStream();
        $uploadInstructions = $initializeResponse->value['uploadInstructions'];
        $uploadedPartIds = [];

        foreach ($uploadInstructions as $instruction) {
            $startOffset = $instruction['firstByte'];
            $endOffset = $instruction['lastByte'];
            $length = $endOffset - $startOffset + 1;

            rewind($readStream['stream']);

            $partialFile = stream_get_contents($readStream['stream'], $length, $startOffset);

            $uploadResponse = $this->getHttpClient()::withHeaders($this->httpHeadersNext())
                ->withBody($partialFile, 'application/octet-stream')
                ->put($instruction['uploadUrl']);

            $uploadResponse = $this->buildResponse($uploadResponse, function () use ($uploadResponse) {
                return [
                    'ETag' => $uploadResponse->header('ETag'),
                ];
            });

            // If one of the upload responses has an error, stop the script, close and delete the stream resource
            if ($uploadResponse->hasError()) {
                Util::closeAndDeleteStreamResource($readStream);

                return $uploadResponse;
            }

            $uploadedPartIds[] = $uploadResponse->ETag;
        }

        Util::closeAndDeleteStreamResource($readStream);

        if (isset($thumb) && $thumbUploadUrl = $initializeResponse->value['thumbnailUploadUrl'] ?? null) {
            $thumbReadStream = $thumb->readStream();

            // For the thumbnail, no need to check the status, if it fails, it will be ignored
            $this->getHttpClient()::withHeaders(['media-type-family' => 'STILLIMAGE'])
                ->withBody($thumbReadStream['stream'], 'application/octet-stream')
                ->put($thumbUploadUrl);

            Util::closeAndDeleteStreamResource($thumbReadStream);
        }

        $finalizeUploadResponse = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeadersNext())
            ->post("$this->apiUrl/rest/videos?action=finalizeUpload", [
                'finalizeUploadRequest' => [
                    'video' => $initializeResponse->value['video'],
                    'uploadToken' => $initializeResponse->value['uploadToken'],
                    'uploadedPartIds' => $uploadedPartIds,
                ],
            ]);

        return $this->buildResponse($finalizeUploadResponse)->useContext([
            'id' => $initializeResponse->value['video'],
        ]);
    }
}
