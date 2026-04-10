<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Inovector\Mixpost\Exceptions\CurrentWorkspaceCouldNotBeDetermined;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\ServiceManager;
use Inovector\Mixpost\Services\TwitterService;
use Inovector\MixpostEnterprise\Models\WorkspaceService as ServiceModel;

class WorkspaceServiceManager extends ServiceManager
{
    protected function registeredServices(): array
    {
        return [
            TwitterService::class,
        ];
    }

    public function get(string $name, ?string $key = null): mixed
    {
        if (! WorkspaceManager::current()) {
            throw new CurrentWorkspaceCouldNotBeDetermined;
        }

        $defaultPayload = [
            'configuration' => $this->getServiceClass($name)::form(),
            'active' => false,
        ];

        $value = $this->getFromCache($name, function () use ($name, $defaultPayload) {
            $dbRecord = ServiceModel::where('name', $name)->first();

            try {
                $payload = $dbRecord ? [
                    'configuration' => array_merge($defaultPayload['configuration'], $dbRecord->configuration->toArray()),
                    'active' => $dbRecord->active ?? false,

                ] : $defaultPayload;

                $this->put($name, $payload['configuration'], $payload['active']);

                return $payload;
            } catch (DecryptException $exception) {
                $this->logDecryptionError($name, $exception);

                return $defaultPayload;
            }
        });

        // Decrypt the configuration from the cache
        if (! is_array($value['configuration'] ?? [])) {
            try {
                $value = array_merge($value, [
                    'configuration' => json_decode(Crypt::decryptString($value['configuration']), true),
                ]);
            } catch (DecryptException $exception) {
                $this->logDecryptionError($name, $exception);

                $value = $defaultPayload;
            }
        }

        if ($key) {
            return Arr::get($value, $key);
        }

        return $value;
    }

    protected function resolveCacheKey(string $name): string
    {
        if (! WorkspaceManager::current()) {
            throw new CurrentWorkspaceCouldNotBeDetermined;
        }

        return $this->config->get('mixpost.cache_prefix').'.workspace-'.WorkspaceManager::current()->id.".services.$name";
    }
}
