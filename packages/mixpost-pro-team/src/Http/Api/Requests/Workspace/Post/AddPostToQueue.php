<?php

namespace Inovector\Mixpost\Http\Api\Requests\Workspace\Post;

use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\AddPostToQueue as BaseAddPostToQueue;

class AddPostToQueue extends BaseAddPostToQueue
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'timezone' => ['sometimes', 'timezone'],
        ]);
    }
}
