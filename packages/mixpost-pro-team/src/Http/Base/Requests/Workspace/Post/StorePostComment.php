<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Concerns\UsesPostActivities;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\PostActivity;

class StorePostComment extends PostFormRequest
{
    use UsesPostActivities;

    public Post $post;

    public ?PostActivity $parentActivity = null;

    public function rules(): array
    {
        return [
            'text' => ['required', 'string'],
            'parent_id' => [
                'nullable',
                'sometimes',
                'string',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $this->post = Post::firstOrFailByUuid($this->route('post'));

        $validator->after(function ($validator) {
            if ($this->input('parent_id')) {
                $this->parentActivity = self::getActivity($this->post, $this->input('parent_id'));

                if (! $this->parentActivity || ! $this->parentActivity->isComment()) {
                    $validator->errors()->add('parent_id', 'Parent comment not found');
                }
            }
        });
    }

    public function handle(): PostActivity
    {
        return $this->post->storeComment(
            user: Auth::id(),
            text: $this->input('text'),
            parent: $this->parentActivity ?? null
        )->load('user');
    }
}
