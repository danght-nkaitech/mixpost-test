<?php

namespace Inovector\Mixpost\SocialProviders\Twitter\Concerns;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait ManagesResources
{
    public function getAccount(): SocialProviderResponse
    {
        $response = $this->connection->get('users/me', ['user.fields' => 'profile_image_url,created_at,verified,verified_type']);

        return $this->buildResponse($response, function () use ($response) {
            return [
                'id' => $response->data->id,
                'name' => $response->data->name,
                'username' => $response->data->username,
                'image' => str_replace('normal', '400x400', $response->data->profile_image_url),
                'data' => [
                    'verified' => $response->data->verified,
                ],
            ];
        });
    }

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        try {
            $mediaResult = $this->uploadMedia($media);
        } catch (Exception $exception) {
            return $this->response(SocialProviderResponseStatus::ERROR, [$exception->getMessage()]);
        }

        if (! empty($mediaResult['errors'])) {
            return $this->response(SocialProviderResponseStatus::ERROR, $mediaResult['errors']);
        }

        return match ($this->getTier()) {
            'legacy' => $this->storePostWithApiV1($text, $mediaResult, $params),
            default => $this->storePostWithApiV2($text, $mediaResult, $params),
        };
    }

    public function uploadMedia(Collection $media): array
    {
        $this->connection->setApiVersion('1.1');

        $ids = [];
        $errors = [];

        foreach ($media as $item) {
            $chunkUpload = $item->isVideo() || $item->isImageGif();

            if (! $chunkUpload) {
                $result = $this->connection->upload('media/upload', [
                    'media' => $item->isLocalAdapter() ? $item->getFullPath() : $item->getUrl(),
                    'media_type' => $item->mime_type,
                    'media_category' => 'tweet_image',
                    'total_bytes' => $item->size,
                ]);
            }

            if ($chunkUpload) {
                /** @var string|array $mediaFilePath * */
                $mediaFilePath = $item->isLocalAdapter() ?
                    $item->getFullPath() :
                    $item->downloadToTemp();

                $result = $this->connection->upload('media/upload', [
                    'media' => $mediaFilePath['fullPath'] ?? $mediaFilePath,
                    'media_type' => $item->mime_type,
                    'media_category' => $item->isImageGif() ? 'tweet_gif' : 'tweet_video',
                    'total_bytes' => $item->size,
                ], ['chunkedUpload' => true]);

                if (isset($mediaFilePath['temporaryDirectory'])) {
                    $mediaFilePath['temporaryDirectory']->delete();
                }
            }

            if (! $result) {
                $errors[] = $result;

                continue;
            }

            // Check status of uploaded media
            if (isset($result->processing_info)) {
                $state = $result->processing_info->state;
                $sleepSeconds = $result->processing_info->check_after_secs;

                do {
                    sleep($sleepSeconds);

                    $mediaStatus = $this->connection->mediaStatus($result->media_id);

                    $state = $mediaStatus->processing_info->state;
                    $sleepSeconds = $mediaStatus->processing_info->check_after_secs ?? 1;

                } while (in_array($state, ['pending', 'in_progress']));

                if ($state === 'failed') {
                    $errors[] = 'upload_failed';

                    continue;
                }
            }

            if ($item->alt_text) {
                $this->connection->mediaMetadataCreate(
                    media_id: $result->media_id_string,
                    alt_text: $item->alt_text
                );
            }

            $ids[] = $result->media_id_string;
        }

        return [
            'ids' => $ids,
            'errors' => $errors,
        ];
    }

    protected function storePostWithApiV1(string $text, array $mediaResult, array $params): SocialProviderResponse
    {
        $this->connection->setApiVersion('1.1');

        $postParameters = ['status' => $text];

        if ($lastId = $params['last_id'] ?? null) {
            $postParameters['in_reply_to_status_id'] = $lastId;
        }

        if (! empty($mediaResult['ids'])) {
            $postParameters['media_ids'] = implode(',', $mediaResult['ids']);
        }

        $postResult = $this->connection->post('statuses/update', $postParameters);

        $httpCode = $this->connection->getLastHttpCode();

        if ($httpCode !== 201) {
            Mixpost::reportException(new Exception(json_encode($postResult) ?: 'An error occurred while creating the post.'));

            $error = $postResult->errors[0]->message ?? (json_encode($postResult) ?: 'An error occurred while creating the post.');

            return $this->response(SocialProviderResponseStatus::ERROR, [$error]);
        }

        return $this->buildResponse($postResult, function () use ($postResult) {
            return [
                'id' => $postResult->id,
            ];
        });
    }

    protected function storePostWithApiV2(string $text, array $mediaResult, array $params): SocialProviderResponse
    {
        $this->connection->setApiVersion('2');

        $postParameters = ['text' => $text];

        if ($lastId = $params['last_id'] ?? null) {
            $postParameters['reply']['in_reply_to_tweet_id'] = $lastId;
        }

        if (! empty($mediaResult['ids'])) {
            $postParameters['media']['media_ids'] = $mediaResult['ids'];
        }

        $postResult = $this->connection->post('tweets', $postParameters, ['jsonPayload' => true]);

        if ($this->connection->getLastHttpCode() !== 201) {
            Mixpost::reportException(new Exception(json_encode($postResult) ?: 'An error occurred while creating the post.'));

            $title = $postResult->title ?? '';
            $detail = $postResult->detail ?? '';

            $error = $title === 'CreditsDepleted'
                ? $title
                : (trim("$title: $detail") ?: 'An error occurred while creating the post.');

            return $this->response(SocialProviderResponseStatus::ERROR, [$error]);
        }

        return $this->buildResponse($postResult, function () use ($postResult) {
            return [
                'id' => $postResult->data->id,
            ];
        });
    }

    public function getAccountMetrics(): SocialProviderResponse
    {
        $response = $this->connection->get('users/me', ['user.fields' => 'public_metrics']);

        return $this->buildResponse($response, function () use ($response) {
            return [
                'followers_count' => $response->data->public_metrics->followers_count,
                'following_count' => $response->data->public_metrics->following_count,
                'tweet_count' => $response->data->public_metrics->tweet_count,
                'listed_count' => $response->data->public_metrics->listed_count,
            ];
        });
    }

    public function getUserTweetTimeline(string $userId, string $paginationToken = ''): SocialProviderResponse
    {
        $params = [
            'tweet.fields' => 'public_metrics,created_at,in_reply_to_user_id',
            'start_time' => Carbon::now('UTC')->subMonths(3)->startOfDay()->toRfc3339String(),
            'exclude' => 'retweets,replies',
            'max_results' => 100,
        ];

        if ($paginationToken) {
            $params['pagination_token'] = $paginationToken;
        }

        $response = $this->connection->get("users/$userId/tweets", $params);

        return $this->buildResponse($response, function () use ($response) {
            return [
                'data' => $response->data ?? [],
                'meta' => $response->meta ?? null,
            ];
        });
    }

    public function deletePost(string $id, array $params = []): SocialProviderResponse
    {
        $this->connection->setApiVersion('2');

        $response = $this->connection->delete("tweets/$id");

        return $this->buildResponse($response);
    }
}
