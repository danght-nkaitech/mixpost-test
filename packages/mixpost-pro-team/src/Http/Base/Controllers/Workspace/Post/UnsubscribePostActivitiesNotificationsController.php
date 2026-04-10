<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Models\Post;

class UnsubscribePostActivitiesNotificationsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $post = Post::firstOrFailByUuid($request->route('post'));

        $post->unsubscribeFromActivitiesNotifications(user: Auth::id());

        return redirect()->back();
    }
}
