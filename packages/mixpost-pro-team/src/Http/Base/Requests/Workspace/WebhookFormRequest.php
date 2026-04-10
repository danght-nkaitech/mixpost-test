<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\WebhookManager;

abstract class WebhookFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'callback_url' => ['required', 'url', 'max:255'],
            'method' => ['required', 'string', 'in:get,post,put,delete'],
            'content_type' => ['required', 'string', 'in:application/json,application/x-www-form-urlencoded'],
            'max_attempts' => ['required', 'integer', 'in:1,2,3'],
            'secret' => ['sometimes', 'max:255'],
            'events' => ['required', 'array'],
            'events.*' => [
                'required',
                'string',
                Rule::in(WorkspaceManager::current() ?
                    WebhookManager::getWorkspaceSelectionOptionKeys() :
                    WebhookManager::getSystemSelectionOptionKeys()),
            ],
            'active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'events' => array_values(
                Arr::where($this->input('events', []), function ($event) {
                    if (WorkspaceManager::current()) {
                        return in_array($event, WebhookManager::getWorkspaceSelectionOptionKeys());
                    }

                    return in_array($event, WebhookManager::getSystemSelectionOptionKeys());
                })
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('mixpost::general.name')]),
            'callback_url.url' => __('validation.url', ['attribute' => __('mixpost::webhook.callback_url')]),
        ];
    }
}
