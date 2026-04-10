<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Post;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inovector\Mixpost\Models\Post;

class SubscribePostActivitiesNotificationsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $post = Post::firstOrFailByUuid($request->route('post'));

        if ($post->hasNotificationSubscriptionForActivities(user: Auth::id())) {
            return redirect()->back();
        }

        $post->subscribeToActivitiesNotifications(user: Auth::id());

        return redirect()->back();
    }
}
