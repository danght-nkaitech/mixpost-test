<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\User;

class UpdateUserRole extends FormRequest
{
    use UsesAuth;

    public function authorize(): bool
    {
        return (int) $this->route('user') !== (int) self::getAuthGuard()->id()
            && ! WorkspaceManager::current()->isOwner(User::findOrFail($this->route('user')));
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(Arr::map(WorkspaceUserRole::cases(), fn ($item) => $item->value))],
            'can_approve' => ['required', 'boolean'],
        ];
    }

    public function handle(): void
    {
        WorkspaceManager::current()
            ->users()
            ->updateExistingPivot($this->route('user'), [
                'role' => $this->input('role'),
                'can_approve' => $this->input('can_approve', false),
            ]);
    }
}
