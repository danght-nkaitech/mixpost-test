<?php

namespace Inovector\Mixpost\Actions\Post;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Post;

class RedirectAfterDeletedPost
{
    public function __invoke(Request $request): RedirectResponse
    {
        $hasFilterFailedStatus = $request->has('status') && $request->get('status') === Str::lower(PostStatus::FAILED->name);

        if ($hasFilterFailedStatus) {
            if (! Post::failed()->exists()) {
                return redirect()->route('mixpost.posts.index', ['workspace' => WorkspaceManager::current()->uuid]);
            }

            return redirect()->back();
        }

        return redirect()->back();
    }
}
