<?php

namespace Inovector\Mixpost\SocialProviders\Google\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\SocialProviderPostOptions;

class GBPPostOptions extends SocialProviderPostOptions
{
    public function rules(FormRequest $request): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:post,offer,event'],
            'button' => ['sometimes', 'string', 'in:NONE,BOOK,ORDER,SHOP,LEARN_MORE,SIGN_UP,CALL'],
            // TODO: Add rules for api/* requests
        ];
    }

    public function map(array $options = []): array
    {
        return [
            // Global
            'type' => Arr::get($options, 'type', 'post'),
            'button' => Arr::get($options, 'button', 'NONE'),
            'button_link' => Arr::get($options, 'button_link', ''),

            // Offer
            'offer_has_details' => Arr::get($options, 'offer_has_details', false),
            'coupon_code' => Arr::get($options, 'coupon_code', ''),
            'offer_link' => Arr::get($options, 'offer_link', ''),
            'terms' => Arr::get($options, 'terms', ''),

            // Event
            'event_title' => Arr::get($options, 'event_title', ''),
            'start_date' => Arr::get($options, 'start_date', ''),
            'end_date' => Arr::get($options, 'end_date', ''),
            'event_has_time' => Arr::get($options, 'event_has_time', false),
            'start_time' => Arr::get($options, 'start_time', ''),
            'end_time' => Arr::get($options, 'end_time', ''),
        ];
    }
}
