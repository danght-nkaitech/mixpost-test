<?php

namespace Inovector\MixpostEnterprise\FeatureLimitResources;

use Inovector\Mixpost\Models\Account;
use Inovector\MixpostEnterprise\Abstracts\FeatureLimitResource;
use Inovector\MixpostEnterprise\FeatureLimitFormFields\CountNumber;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

class NumberOfBrandsSocialAccounts extends FeatureLimitResource
{
    public string $name = 'No. of Brands Social Accounts';

    public string $description = 'Each brand provides full brand management, where the user connects all social media accounts associated with that brand.';

    public function form(): array
    {
        return [
            CountNumber::make('count')->default(function () {
                return 1;
            }),
        ];
    }

    public function validator(?object $data = null): FeatureLimitResponse
    {
        $value = $this->getValue('count');

        if ($value === null) {
            return $this->makePasses();
        }

        $count = Account::byWorkspace($data->workspace)
            ->provider($data->provider)
            ->count();

        // When adding account entities
        if (isset($data->items)) {
            $totalCount = $count + count($data->items ?? []);

            if ($totalCount > (int) $value) {
                return $this->makeFails()
                    ->withMessages(__('mixpost-enterprise::feature_limit.max_brand_account').$value);
            }
        }

        if ($count < (int) $value) {
            return $this->makePasses();
        }

        return $this->makeFails()
            ->withMessages(__('mixpost-enterprise::feature_limit.max_account', ['value' => $value]));
    }
}
