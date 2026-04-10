<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = WorkspaceManager::current();

        if (! $workspace) {
            abort(403);
        }

        if ($workspace->unlimitedAccess()) {
            return $next($request);
        }

        if ($workspace->locked()) {
            return redirect()
                ->route('mixpost_e.workspace.locked', ['workspace' => $workspace->uuid]);
        }

        if ($workspace->hasGenericSubscription() && ! $workspace->genericSubscriptionFree() && $workspace->hasExpiredGenericTrial()) {
            return redirect()
                ->route('mixpost_e.workspace.trialEnded', ['workspace' => $workspace->uuid]);
        }

        $subscription = $workspace->subscription('default');

        if (! $subscription && ! $workspace->hasGenericSubscription()) {
            return redirect()
                ->route('mixpost_e.workspace.upgrade', ['workspace' => $workspace->uuid]);
        }

        if ($subscription) {
            if ($subscription->paused() && ! $subscription->onPausedGracePeriod()) {
                //  Determine if a user has paused their subscription and not are still on their "grace period"
                return redirect()->route('mixpost_e.workspace.locked', ['workspace' => $workspace->uuid]);
            }

            if ($subscription->ended()) {
                //  The user has cancelled their subscription and is no longer within their "grace period"
                return redirect()->route('mixpost_e.workspace.locked', ['workspace' => $workspace->uuid]);
            }

            if ($subscription->pastDue()) {
                // When a subscription is past due, you should instruct the user to update their payment information.
                return redirect()->route('mixpost_e.workspace.billing', ['workspace' => $workspace->uuid]);
            }
        }

        return $next($request);
    }
}
