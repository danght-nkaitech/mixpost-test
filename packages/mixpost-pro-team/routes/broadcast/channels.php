<?php

use Illuminate\Support\Facades\Broadcast;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;

Broadcast::channel('mixpost_posts.{uuid}', function ($user, $uuid) {
    if (! $user) {
        return false;
    }

    if (! $post = Post::withoutWorkspace()->where('uuid', $uuid)->first()) {
        return false;
    }

    $finalUserModel = app(Util::getUserClass())::make($user->only('email'))->setAttribute('id', $user->id);

    if ($post->workspace && ! $finalUserModel->hasWorkspace($post->workspace->id)) {
        return false;
    }

    return true;
}, ['guards' => [Util::config('auth_guard')]]);
