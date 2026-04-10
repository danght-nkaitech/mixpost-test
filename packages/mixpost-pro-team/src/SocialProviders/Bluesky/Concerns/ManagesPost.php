<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\SocialProviders\Bluesky\Helpers;
use Inovector\Mixpost\Support\FetchUrlCard;
use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Support\TemporaryFile;
use RuntimeException;

trait ManagesPost
{
    use UsesAuthServer;
    use UsesOAuthAgent;
    use UsesUploads;
    use UsesValues;

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        try {
            $parsed = Helpers::parseFacetsAndShortenText($text, $this->getServer());

            $postParams = [
                '$type' => 'app.bsky.feed.post',
                'text' => $parsed['text'],
                'facets' => $parsed['facets'],
                'createdAt' => Carbon::now()->utc(),
            ];

            $this->handleReplies($params, $postParams);

            $this->embedUrlCard($media, $params, $postParams);

            if ($video = $media->first(fn ($media) => $media->isVideo())) {
                $this->embedVideo($video, $postParams);
            } else {
                $this->embedImages($media->filter(fn ($media) => $media->isImage()), $postParams);
            }
        } catch (RuntimeException $e) {
            return $this->response(SocialProviderResponseStatus::ERROR, json_decode($e->getMessage(), true));
        }

        $response = $this->http()->post('com.atproto.repo.createRecord', [
            'repo' => $this->getDid(),
            'collection' => 'app.bsky.feed.post',
            'record' => $postParams,
        ]);

        return $this->buildResponse($response, function ($data) {
            return [
                'id' => $data['cid'],
                'data' => [
                    'uri' => $data['uri'],
                ],
            ];
        });
    }

    public function deletePost(string $id, array $params = []): SocialProviderResponse
    {
        $response = $this->http()->post('com.atproto.repo.deleteRecord', [
            'repo' => $this->getDid(),
            'collection' => 'app.bsky.feed.post',
            'rkey' => Str::afterLast($params['uri'], '/'),
        ]);

        return $this->buildResponse($response);
    }

    private function handleReplies(array $params, array &$postParams): void
    {
        /** @var SocialProviderResponse|null $root */
        $root = $params['first_response'] ?? null;

        /** @var SocialProviderResponse|null $parent */
        $parent = $params['last_response'] ?? null;

        if ($root && $parent) {
            $postParams['reply'] = [
                'root' => [
                    'uri' => $root->data['uri'],
                    'cid' => $root->id(),
                ],
                'parent' => [
                    'uri' => $parent->data['uri'],
                    'cid' => $parent->id(),
                ],
            ];
        }
    }

    private function embedImages(Collection $images, array &$postParams): void
    {
        $blobs = $images->map(function ($media) {
            $response = $this->uploadBlob($media);

            if ($response->hasError()) {
                throw new RuntimeException(json_encode($response->context()));
            }

            if (! $response->blob) {
                return null;
            }

            return [
                'image' => $response->blob,
                'alt' => $media->alt_text ?? '',
            ];
        })->filter();

        if ($blobs->isEmpty()) {
            return;
        }

        $postParams['embed'] = [
            '$type' => 'app.bsky.embed.images',
            'images' => $blobs->toArray(),
        ];
    }

    private function embedVideo(Media $video, array &$postParams): void
    {
        $response = $this->uploadBlob($video);

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        $postParams['embed'] = [
            '$type' => 'app.bsky.embed.video',
            'video' => $response->blob,
        ];
    }

    private function embedUrlCard(Collection $media, array $params, array &$postParams): void
    {
        if ($media->count()) {
            return;
        }

        if (! $url = $params['url'] ?? '') {
            return;
        }

        $card = (new FetchUrlCard)($url);

        if (! $card['default']['title']) {
            return;
        }

        $postParams['embed'] = [
            '$type' => 'app.bsky.embed.external',
            'external' => [
                'uri' => $url,
                'title' => $card['default']['title'],
                'description' => $card['default']['description'] ?? '',
            ],
        ];

        if ($card['default']['image']) {
            $file = null;

            try {
                $file = TemporaryFile::make()->fromUrl($card['default']['image']);
                $response = $this->uploadBlob($file);

                if (! $response->hasError()) {
                    $postParams['embed']['external']['thumb'] = $response->blob;
                }
            } finally {
                $file?->directory()->delete();
            }
        }
    }
}
