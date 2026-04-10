<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\WorkspaceManager;

class CancelInvitation extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function handle(): bool
    {
        $invitation = WorkspaceManager::current()
            ->invitations()
            ->where('uuid', $this->route('invitation'))
            ->first();

        if (! $invitation) {
            return false;
        }

        return $invitation->delete();
    }
}
