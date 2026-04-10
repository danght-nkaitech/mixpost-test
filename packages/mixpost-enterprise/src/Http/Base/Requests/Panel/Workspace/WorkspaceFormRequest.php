<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Models\User;
use Inovector\Mixpost\Rules\HexRule;
use Inovector\MixpostEnterprise\Enums\WorkspaceAccessStatus;

class WorkspaceFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'hex_color' => ['required', new HexRule],
            'owner_id' => ['nullable', 'exists:'.User::class.',id'],
            'access_status' => ['required', Rule::in(Arr::map(WorkspaceAccessStatus::cases(), fn ($item) => $item->value))],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_id.exists' => __('The selected owner is invalid.'),
        ];
    }

    public function requestData(): array
    {
        return [
            'name' => $this->input('name'),
            'hex_color' => Str::after($this->input('hex_color'), '#'),
            'owner_id' => $this->input('owner_id'),
            'access_status' => $this->input('access_status'),
        ];
    }
}
