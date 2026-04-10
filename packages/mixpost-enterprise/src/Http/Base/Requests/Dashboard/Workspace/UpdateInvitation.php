<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Facades\WorkspaceManager;

class UpdateInvitation extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(Arr::map(WorkspaceUserRole::cases(), fn ($item) => $item->value))],
        ];
    }

    public function handle(): void
    {
        $invitation = WorkspaceManager::current()
            ->invitations()
            ->where('uuid', $this->route('invitation'))
            ->first();

        if (! $invitation) {
            return;
        }

        $invitation->update([
            'role' => $this->input('role'),
        ]);
    }
}
