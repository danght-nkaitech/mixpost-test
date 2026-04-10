<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Workspace\DestroyWorkspace as DestroyWorkspaceAction;

class DestroyWorkspace extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value !== WorkspaceManager::current()->name) {
                        $fail(__('mixpost-enterprise::workspace.workspace_name_invalid'));
                    }
                },
            ],
        ];
    }

    public function handle(): void
    {
        (new DestroyWorkspaceAction)(WorkspaceManager::current());
    }
}
