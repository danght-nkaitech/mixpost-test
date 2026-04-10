<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\User;

class DetachUser extends FormRequest
{
    use UsesAuth;

    public function authorize(): bool
    {
        return (int) $this->route('user') !== (int) self::getAuthGuard()->id()
            && ! WorkspaceManager::current()->isOwner(User::findOrFail($this->route('user')));
    }

    public function rules(): array
    {
        return [];
    }

    public function handle(): void
    {
        WorkspaceManager::current()
            ->users()
            ->detach($this->route('user'));
    }
}
