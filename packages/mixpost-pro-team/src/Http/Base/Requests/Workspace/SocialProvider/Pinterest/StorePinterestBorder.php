<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\SocialProvider\Pinterest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\SocialProviders\Pinterest\PinterestProvider;
use Inovector\Mixpost\Support\SocialProviderResponse;

class StorePinterestBorder extends FormRequest
{
    use UsesSocialProviderManager;

    public Account $account;

    public function rules(): array
    {
        return [
            'account_uuid' => ['required', 'string'],
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->account = Account::firstOrFailByUuid($this->input('account_uuid'));

        $validator->after(function ($validator) {
            if ($this->account->provider !== 'pinterest') {
                $validator->errors()->add('invalid_provider', __('mixpost::service.pinterest.not_account'));
            }
        });
    }

    public function handle(): SocialProviderResponse
    {
        /**
         * @var $response SocialProviderResponse
         *
         * @see PinterestProvider
         */
        $response = $this->connectProvider($this->account)->createBoard($this->input('name'));

        if ($response->hasError()) {
            return $response;
        }

        $currentBoards = Arr::get($this->account->data, 'relationships.boards', []);

        $boards = array_merge($currentBoards, [[
            'id' => $response->id,
            'name' => $response->name,
        ]]);

        // TODO: improve this update
        // It could overlap some existing data in ->data.
        $this->account->update([
            'data' => [
                'relationships' => [
                    'boards' => $boards,
                ],
            ],
        ]);

        return $response;
    }
}
