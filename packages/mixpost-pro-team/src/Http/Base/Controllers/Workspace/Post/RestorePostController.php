<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Post;

class RestorePostController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $post = Post::firstOrFailTrashedByUuid($request->route('post'));

        $post->restore();

        return redirect()->back();
    }
}
