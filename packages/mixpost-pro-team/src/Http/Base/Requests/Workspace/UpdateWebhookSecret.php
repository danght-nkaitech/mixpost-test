<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Webhook;

class UpdateWebhookSecret extends FormRequest
{
    public function rules(): array
    {
        return [
            'secret' => ['required', 'string', 'max:255'],
        ];
    }

    public function handle(): bool
    {
        $record = Webhook::firstOrFailByUuid($this->route('webhook'));

        if (WorkspaceManager::current() && $record->isSystem()) {
            abort(404);
        }

        return $record->update([
            'secret' => $this->input('secret'),
        ]);
    }

    public function messages(): array
    {
        return [
            'secret.required' => __('validation.required', ['attribute' => __('mixpost::webhook.secret')]),
        ];
    }
}
