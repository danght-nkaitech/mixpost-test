<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Contracts\AccountResource as AccountResourceContract;
use Inovector\Mixpost\Enums\SocialProviderContentType;

class AccountResource extends JsonResource implements AccountResourceContract
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name.($this->suffix() ? " ({$this->suffix()})" : ''),
            'suffix' => $this->suffix(),
            'username' => $this->username,
            'image' => $this->image(),
            'provider' => $this->provider,
            'provider_name' => $this->providerName(),
            'post_configs' => $this->postConfigs(),
            'relationships' => $this->relationships(),
            'data' => $this->data,
            'authorized' => $this->authorized,
            'created_at' => $this->created_at->diffForHumans(),
            'content_type' => $this->contentType(),
            'external_account_url' => $this->getExternalAccountUrl(),
            'external_url' => $this->whenPivotLoaded('mixpost_post_accounts', function () {
                if (! $this->pivot->provider_post_id) {
                    return null;
                }

                return $this->getExternalPostUrl();
            }),
            'support_analytics' => $this->supportAnalytics(),
            'errors' => $this->whenPivotLoaded('mixpost_post_accounts', function () {
                return $this->errors();
            }),
        ];
    }

    protected function errors(): array
    {
        if ($this->pivot->errors) {
            $errors = Arr::wrap(json_decode($this->pivot->errors, true));

            return array_map(function ($error) {
                if (is_string($error) && str_starts_with($error, '$t:')) {
                    $translationKey = substr($error, 3);

                    return __($translationKey);
                }

                if (is_string($error)) {
                    return $this->getErrorMessage($error);
                }

                return $error;
            }, $errors);
        }

        return [];
    }

    protected function getExternalPostUrl(): ?string
    {
        $provider = $this->resource->getProviderClass();

        if (! ($provider && method_exists($provider, 'externalPostUrl'))) {
            return '';
        }

        return $provider::externalPostUrl($this);
    }

    protected function getExternalAccountUrl(): string
    {
        $provider = $this->resource->getProviderClass();

        if (! ($provider && method_exists($provider, 'externalAccountUrl'))) {
            return '';
        }

        return $provider::externalAccountUrl($this);
    }

    protected function contentType(): SocialProviderContentType
    {
        if ($provider = $this->resource->getProviderClass()) {
            return $provider::contentType($this);
        }

        return SocialProviderContentType::SINGLE;
    }

    protected function supportAnalytics()
    {
        if ($provider = $this->resource->getProviderClass()) {
            return $provider::supportAnalytics();
        }

        return true;
    }

    protected function getErrorMessage(string $key): string
    {
        if ($provider = $this->resource->getProviderClass()) {
            return $provider::mapErrorMessage($key);
        }

        return $key;
    }
}
