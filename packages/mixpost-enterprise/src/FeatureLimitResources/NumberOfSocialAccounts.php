<?php

namespace Inovector\MixpostEnterprise\FeatureLimitResources;

use Inovector\Mixpost\Models\Account;
use Inovector\MixpostEnterprise\Abstracts\FeatureLimitResource;
use Inovector\MixpostEnterprise\FeatureLimitFormFields\CountNumber;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

class NumberOfSocialAccounts extends FeatureLimitResource
{
    public string $name = 'No. of Social Accounts';

    public function form(): array
    {
        return [
            CountNumber::make('count')->default(function () {
                return 4;
            }),
        ];
    }

    public function validator(?object $data = null): FeatureLimitResponse
    {
        $value = $this->getValue('count');

        if ($value === null) {
            return $this->makePasses();
        }

        $count = Account::byWorkspace($data->workspace)->count();

        // When adding account entities
        if (isset($data->items)) {
            $totalCount = $count + count($data->items ?? []);

            if ($totalCount > (int) $value) {
                return $this->makeFails()
                    ->withMessages(__('mixpost-enterprise::feature_limit.max_social_accounts', ['value' => $value]));
            }
        }

        if ($count < (int) $value) {
            return $this->makePasses();
        }

        return $this->makeFails()
            ->withMessages(__('mixpost-enterprise::feature_limit.max_social_accounts', ['value' => $value]));
    }
}
