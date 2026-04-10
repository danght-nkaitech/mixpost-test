<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Models\Workspace;

class AttachWorkspaceUser extends FormRequest
{
    use UsesUserModel;

    public ?Workspace $workspace = null;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:'.app(self::getUserClass())->getTable().',id',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->getWorkspace()->users()->where('user_id', $value)->exists()) {
                        $fail('This user is already a member of this workspace.');
                    }
                },
            ],
            'role' => ['required', Rule::in(Arr::map(WorkspaceUserRole::cases(), fn ($item) => $item->value))],
            'can_approve' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => __('mixpost::user.select_user'),
        ];
    }

    public function handle(): void
    {
        $this->getWorkspace()->attachUser(
            id: $this->input('user_id'),
            role: WorkspaceUserRole::fromName(Str::upper($this->input('role'))),
            canApprove: $this->input('can_approve', false),
        );
    }

    protected function getWorkspace(): Workspace
    {
        if (! $this->workspace) {
            $this->workspace = Workspace::firstOrFailByUuid($this->route('workspace'));
        }

        return $this->workspace;
    }
}
