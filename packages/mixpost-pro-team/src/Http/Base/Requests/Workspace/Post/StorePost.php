<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Post;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Events\Post\PostCreated;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;

class StorePost extends PostFormRequest
{
    public function handle()
    {
        $record = DB::transaction(function () {
            $post = Post::create([
                'user_id' => Auth::id(),
                'status' => PostStatus::DRAFT,
                'scheduled_at' => $this->scheduledAt() ? Util::convertTimeToUTC($this->scheduledAt()) : null,
            ]);

            $post->accounts()->attach($this->input('accounts', []));
            $post->tags()->attach($this->input('tags'));
            $post->versions()->createMany($this->inputVersions());

            return $post;
        });

        PostCreated::dispatch($record);

        return $record;
    }
}
