<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Util;

trait ManagesYoutubeResources
{
    protected string $apiVersion = 'v3';

    protected string $apiUrl = 'https://www.googleapis.com/youtube';

    protected string $apiUploadUrl = 'https://www.googleapis.com/upload/youtube';

    public function getAccount(): SocialProviderResponse
    {
        $response = $this->getEntities();

        if ($response->hasError()) {
            return $response;
        }

        $filter = array_values(Arr::where($response->context(), function ($entity) {
            return $entity['id'] === $this->values['provider_id'];
        }));

        return new SocialProviderResponse(SocialProviderResponseStatus::OK, $filter[0] ?? []);
    }

    public function getEntities(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->get("$this->apiUrl/$this->apiVersion/channels", [
                'part' => 'snippet',
                'maxResults' => 50,
                'mine' => true,
            ]);

        return $this->buildResponse($response, function () use ($response) {
            return $response->collect('items')->map(function ($item) {
                return [
                    'id' => Arr::get($item, 'id'),
                    'name' => Arr::get($item, 'snippet.title'),
                    'username' => Str::of(Arr::get($item, 'snippet.customUrl', ''))->replace('@', ''),
                    'image' => Arr::get($item, 'snippet.thumbnails.medium.url'),
                ];
            })->toArray();
        });
    }

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        /** @var $mediaItem Media * */
        $mediaItem = $media->first();

        if (! $mediaItem?->isVideo()) {
            return $this->response(SocialProviderResponseStatus::ERROR, ['video_not_selected']);
        }

        $data = json_encode([
            'snippet' => [
                'title' => $params['title'] ?? '',
                'description' => $text,
            ],
            'status' => [
                'privacyStatus' => $params['status'] ?? 'public',
                'selfDeclaredMadeForKids' => $params['made_for_kids'] ?? false,
            ],
        ]);

        $session = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders([
                'X-Upload-Content-Length' => $mediaItem->size,
                'X-Upload-Content-Type' => 'video/*',
            ])
            ->withBody($data, 'application/json; charset=UTF-8')
            ->post("$this->apiUploadUrl/$this->apiVersion/videos?uploadType=resumable&part=snippet,status");

        if ($session->status() !== 200) {
            return $this->response(SocialProviderResponseStatus::ERROR, $session->json());
        }

        $uploadUrl = $session->header('Location');
        $stream = $mediaItem->readStream();

        $upload = function ($timeout) use ($uploadUrl, $stream) {
            return $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->timeout($timeout)
                ->withBody($stream['stream'], 'video/*')
                ->put($uploadUrl);
        };

        $result = Util::performHttpRequestWithTimeoutRetries($upload, 7 * 60);

        Util::closeAndDeleteStreamResource($stream);

        $videoId = $result->json('id');

        $thumbId = collect($params['video_thumbs'] ?? [])->firstWhere('media_id', $mediaItem->id)['thumb_id'] ?? null;

        if ($thumbId) {
            $customVideoThumb = Media::where('id', $thumbId)->first();

            $session = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->withBody($customVideoThumb->contents(), 'application/octet-stream')
                ->post("$this->apiUploadUrl/$this->apiVersion/thumbnails/set?videoId=$videoId&uploadType=multipart");

            if ($session->status() !== 200) {
                return $this->response(SocialProviderResponseStatus::ERROR, $session->json());
            }
        }

        return $this->buildResponse($result, function () use ($result) {
            $data = $result->json();

            return [
                'id' => $data['id'],
            ];
        });
    }

    public function deletePost(string $id, array $params = []): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $token = $this->getAccessToken()['access_token'];

        $response = $this->getHttpClient()::withToken($token)->delete("$this->apiUrl/$this->apiVersion/videos?id=$id");

        if ($response->notFound()) {
            /**
             * Handle 404 response when attempting to delete a post that no longer exists on the platform.
             * This occurs when we have a stored post_provider_id but the post has already been deleted directly on the platform.
             */
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        return $this->buildResponse($response);
    }
}
