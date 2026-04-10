<?php

namespace Inovector\Mixpost\SocialProviders\Pinterest\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\PostVersionHelpers;
use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Util;

trait ManagesResources
{
    public function getAccount(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $response = $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->get("{$this->getApiUrl()}/$this->apiVersion/user_account")
        );

        if ($response->hasError()) {
            return $response;
        }

        $boards = $this->getBoards();

        $relationships = [];

        if (! $boards->hasError()) {
            $relationships['boards'] = Arr::map($boards->items ?? [], function ($item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                ];
            });
        }

        return $this->response(SocialProviderResponseStatus::OK, [
            'id' => $response->username,
            'name' => $response->business_name ?? $response->username,
            'username' => $response->username,
            'image' => $response->profile_image,
            'data' => [
                'relationships' => $relationships,
            ],
        ]);
    }

    public function getBoards(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $token = $this->getAccessToken()['access_token'];

        $response = $this->getHttpClient()::withToken($token)->get("{$this->getApiUrl()}/$this->apiVersion/boards", [
            'page_size' => 200,
        ]);

        return $this->buildResponse($response);
    }

    public function getAccountMetrics(): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->get("{$this->getApiUrl()}/$this->apiVersion/user_account")
        );
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

        if (! $media->count()) {
            return $this->response(SocialProviderResponseStatus::ERROR, ['no_media_selected']);
        }

        // Get board id only for the current account
        $params['board_id'] = Arr::get($params, "boards.account-{$this->values['account_id']}");

        $isVideo = $media->count() === 1 && $media->first()->isVideo();

        if (! $isVideo) {
            $mediaData = [
                'media_source' => [
                    'source_type' => 'image_base64',
                    'content_type' => 'image/jpeg',
                    'data' => base64_encode($media->first()->contents()),
                ],
            ];
        }

        if ($isVideo) {
            $uploadVideoResponse = $this->uploadVideo($media->first());

            if ($uploadVideoResponse->hasError()) {
                return $uploadVideoResponse;
            }

            /* @var SocialProviderResponse|null $result */
            $result = Util::performTaskWithDelay(
                task: function () use ($uploadVideoResponse) {
                    $statusResponse = $this->getVideo($uploadVideoResponse->media_id);

                    if ($statusResponse->hasError()) {
                        return $statusResponse;
                    }

                    if ($statusResponse->status === 'registered' || $statusResponse->status === 'processing') {
                        // Return null to continue checking
                        return null;
                    }

                    return $statusResponse;
                },
                initialDelay: 30,
                increaseDelay: false
            );

            if (! $result) {
                return $this->response(SocialProviderResponseStatus::ERROR, ['video_processing_timeout']);
            }

            if ($result->hasError()) {
                return $result;
            }

            if ($result->status !== 'succeeded') {
                return $this->response(SocialProviderResponseStatus::ERROR, $result->context());
            }

            $coverUrl = $media->first()->getThumbUrl();

            // Check if it has custom thumb
            if (isset($params['video_thumbs']) && is_array($params['video_thumbs'])) {
                $customerThumbMedia = PostVersionHelpers::getThumbForMediaId($media->first()->id, $params['video_thumbs']);
                $coverUrl = $customerThumbMedia?->getUrl() ?? $coverUrl;
            }

            $mediaData = [
                'media_source' => [
                    'source_type' => 'video_id',
                    'cover_image_url' => $coverUrl,
                    'media_id' => $uploadVideoResponse->media_id,
                ],
            ];
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->post("{$this->getApiUrl()}/$this->apiVersion/pins", array_merge([
                'link' => $params['link'],
                'title' => $params['title'],
                'board_id' => $params['board_id'],
                'description' => $text,
                'alt_text' => $media->first()->alt_text,
            ], $mediaData));

        return $this->buildResponse($response, function () use ($response) {
            $data = $response->json();

            return [
                'id' => $data['id'],
            ];
        });
    }

    public function uploadVideo(Media $media): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $initResult = $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->post("{$this->getApiUrl()}/$this->apiVersion/media", [
                    'media_type' => 'video',
                ])
        );

        if ($initResult->hasError()) {
            return $initResult;
        }

        $stream = $media->readStream();

        $upload = function ($timeout) use ($initResult, $stream) {
            return $this->getHttpClient()::timeout($timeout)
                ->asMultipart()
                ->attach('file', $stream['stream'])
                ->post($initResult->upload_url, $initResult->upload_parameters);
        };

        $result = Util::performHttpRequestWithTimeoutRetries($upload, 7 * 60);

        Util::closeAndDeleteStreamResource($stream);

        if (! $result) {
            return $this->response(SocialProviderResponseStatus::ERROR, ['request_timeout']);
        }

        return $initResult->useContext([
            'media_id' => $initResult->media_id,
        ]);
    }

    public function getVideo(string $mediaId): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->get("{$this->getApiUrl()}/$this->apiVersion/media/$mediaId")
        );
    }

    public function getPins(string $bookmark = ''): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $data = [
            'pin_filter' => 'exclude_repins',
            'page_size' => 100,
        ];

        if ($bookmark) {
            $data['bookmark'] = $bookmark;
        }

        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->get("{$this->getApiUrl()}/$this->apiVersion/pins", $data)
        );
    }

    public function getPinAnalytics(string $id, array $query): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        return $this->buildResponse(
            $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
                ->get("{$this->getApiUrl()}/$this->apiVersion/pins/$id/analytics", $query)
        );
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

        $response = $this->getHttpClient()::withToken($token)->delete("{$this->getApiUrl()}/$this->apiVersion/pins/$id");

        if ($response->notFound()) {
            /**
             * Handle 404 response when attempting to delete a post that no longer exists on the platform.
             * This occurs when we have a stored post_provider_id but the post has already been deleted directly on the platform.
             */
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        return $this->buildResponse($response);
    }

    public function createBoard(string $name): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $token = $this->getAccessToken()['access_token'];

        return $this->buildResponse(
            $this->getHttpClient()::withToken($token)
                ->post("{$this->getApiUrl()}/$this->apiVersion/boards", [
                    'name' => $name,
                    'privacy' => 'PUBLIC',
                ])
        );
    }
}
