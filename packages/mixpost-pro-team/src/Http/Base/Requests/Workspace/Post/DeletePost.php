<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Enums\PostDeleteMode;
use Inovector\Mixpost\Facades\WorkspaceManager;

class DeletePost extends FormRequest
{
    public function rules(): array
    {
        return [
            'delete_mode' => ['sometimes', 'string', Rule::in([PostDeleteMode::APP_ONLY, PostDeleteMode::SOCIAL_ONLY, PostDeleteMode::APP_AND_SOCIAL])],
            'status' => ['nullable', 'string'],
            'posts' => ['required', 'array'],
            'posts.*' => ['string', WorkspaceManager::existsRule('mixpost_posts', 'uuid')],
        ];
    }
}
