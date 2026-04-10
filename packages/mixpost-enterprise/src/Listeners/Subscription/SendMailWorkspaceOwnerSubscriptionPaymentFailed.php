<?php

namespace Inovector\MixpostEnterprise\Listeners\Subscription;

use Illuminate\Support\Facades\Mail;
use Inovector\Mixpost\Util;
use Inovector\MixpostEnterprise\Mail\SubscriptionPaymentFailed;

class SendMailWorkspaceOwnerSubscriptionPaymentFailed
{
    public function handle(object $event): void
    {
        if (! $event->subscription->workspace) {
            return;
        }

        if (! $event->subscription->workspace->owner) {
            return;
        }

        Mail::to($event->subscription->workspace->owner->email)
            ->locale(Util::config('default_locale'))
            ->send(new SubscriptionPaymentFailed($event->subscription));
    }
}
