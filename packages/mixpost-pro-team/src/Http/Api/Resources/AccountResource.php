<?php

namespace Inovector\Mixpost\Http\Api\Resources;

use Inovector\Mixpost\Http\Base\Resources\AccountResource as BaseAccountResource;

class AccountResource extends BaseAccountResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name.($this->suffix() ? " ({$this->suffix()})" : ''),
            'username' => $this->username,
            'image' => $this->image(),
            'provider' => $this->provider,
            'data' => $this->data,
            'authorized' => $this->authorized,
            'created_at' => $this->created_at->toDateTimeString(),
            'external_url' => $this->whenPivotLoaded('mixpost_post_accounts', function () {
                if (! $this->pivot->provider_post_id) {
                    return null;
                }

                return $this->getExternalPostUrl();
            }),
            'errors' => $this->whenPivotLoaded('mixpost_post_accounts', function () {
                return $this->errors();
            }),
        ];
    }
}
