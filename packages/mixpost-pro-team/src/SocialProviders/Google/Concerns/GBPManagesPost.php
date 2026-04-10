<?php

namespace Inovector\Mixpost\SocialProviders\Google\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\SocialProviderResponse;

trait GBPManagesPost
{
    use GBPUsesValues;

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        if ($this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $data = [
            'summary' => $text,
        ];

        $this->buildPostData($media, $params, $data);

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->post("$this->apiUrl/$this->apiVersion/accounts/{$this->accountId()}/locations/{$this->locationId()}/localPosts", $data);

        return $this->buildResponse($response, function ($data) {
            return [
                'id' => Str::afterLast($data['name'], '/'),
                'data' => [
                    'url' => $data['searchUrl'],
                ],
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

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->delete("$this->apiUrl/$this->apiVersion/accounts/{$this->accountId()}/locations/{$this->locationId()}/localPosts/$id");

        if ($response->notFound()) {
            /**
             * Handle 404 response when attempting to delete a post that no longer exists on the platform.
             * This occurs when we have a stored post_provider_id but the post has already been deleted directly on the platform.
             */
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        return $this->buildResponse($response);
    }

    private function buildPostData(Collection $media, array $params, array &$data): void
    {
        $type = $params['type'] ?? 'post';

        // Call to action
        if ($params['button'] !== 'NONE') {
            $actionType = $params['button'] ?? 'LEARN_MORE';

            $data['callToAction'] = [
                'actionType' => $actionType,
                'url' => $actionType !== 'CALL' ? ($params['button_link'] ?? '') : '',
            ];
        }

        if ($type === 'post') {
            $data['topicType'] = 'STANDARD';
        }

        if ($type === 'offer') {
            $data['topicType'] = 'OFFER';

            if ($params['offer_has_details']) {
                $data['offer'] = [

                    'couponCode' => $params['coupon_code'] ?? '',
                    'termsConditions' => $params['terms'] ?? '',
                    'redeemOnlineUrl' => $params['offer_link'] ?? '',
                ];
            }

            $data['event'] = [
                'title' => Str::limit($params['event_title'] ?? '', 58, ''),
                'schedule' => [
                    'startDate' => [
                        'year' => Carbon::parse($params['start_date'] ?? 'now')->year,
                        'month' => Carbon::parse($params['start_date'] ?? 'now')->month,
                        'day' => Carbon::parse($params['start_date'] ?? 'now')->day,
                    ],
                    'endDate' => [
                        'year' => Carbon::parse($params['end_date'] ?? 'now')->year,
                        'month' => Carbon::parse($params['end_date'] ?? 'now')->month,
                        'day' => Carbon::parse($params['end_date'] ?? 'now')->day,
                    ],
                ],
            ];
        }

        if ($type === 'event') {
            $data['topicType'] = 'EVENT';
            $data['event'] = [
                'title' => Str::limit($params['event_title'] ?? '', 58, ''),
                'schedule' => [
                    'startDate' => [
                        'year' => Carbon::parse($params['start_date'] ?? 'now')->year,
                        'month' => Carbon::parse($params['start_date'] ?? 'now')->month,
                        'day' => Carbon::parse($params['start_date'] ?? 'now')->day,
                    ],
                    'endDate' => [
                        'year' => Carbon::parse($params['end_date'] ?? 'now')->year,
                        'month' => Carbon::parse($params['end_date'] ?? 'now')->month,
                        'day' => Carbon::parse($params['end_date'] ?? 'now')->day,
                    ],
                ],
            ];

            if (! empty($params['start_time'])) {
                $data['event']['schedule']['startTime'] = [
                    'hours' => Carbon::parse($params['start_time'])->hour,
                    'minutes' => Carbon::parse($params['start_time'])->minute,
                    'seconds' => 0,
                    'nanos' => 0,
                ];
            }

            if (! empty($params['end_time'])) {
                $data['event']['schedule']['endTime'] = [
                    'hours' => Carbon::parse($params['end_time'])->hour,
                    'minutes' => Carbon::parse($params['end_time'])->minute,
                    'seconds' => 0,
                    'nanos' => 0,
                ];
            }
        }

        if ($media->filter(fn (Media $item) => $item->isImage())->count()) {
            $data['media'] = $media->filter(fn (Media $item) => $item->isImage())->map(function (Media $mediaItem) {
                return [
                    'mediaFormat' => 'PHOTO',
                    'sourceUrl' => $mediaItem->getUrl(),
                ];
            })->toArray();
        }
    }
}
