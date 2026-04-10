<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Actions\Account\StoreProviderEntitiesAsAccounts as StoreProviderEntitiesAsAccountsAction;
use Inovector\Mixpost\Events\Account\StoringAccountEntities;

class StoreProviderEntities extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
        ];
    }

    public function handle()
    {
        StoringAccountEntities::dispatch($this->route('provider'), $this->input('items'));

        (new StoreProviderEntitiesAsAccountsAction)($this->route('provider'), $this->input('items'));
    }
}
