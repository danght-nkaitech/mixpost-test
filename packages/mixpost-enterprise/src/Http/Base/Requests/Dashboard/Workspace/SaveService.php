<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Http\Base\Requests\Admin\SaveService as BaseSaveService;
use Inovector\MixpostEnterprise\Actions\Workspace\UpdateOrCreateService;
use Inovector\MixpostEnterprise\Facades\WorkspaceServiceManager;

class SaveService extends BaseSaveService
{
    public function handle(): void
    {
        $configuration = Arr::map($this->service()::form(), function ($_, $key) {
            return $this->input("configuration.$key");
        });

        (new UpdateOrCreateService)(
            name: $this->route('service'),
            configuration: $configuration,
            active: $this->input('active', false)
        );
    }

    protected function service(): ?string
    {
        return WorkspaceServiceManager::getServiceClass($this->route('service'));
    }
}
