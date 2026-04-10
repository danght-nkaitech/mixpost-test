<?php

namespace Inovector\MixpostEnterprise\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Concerns\Mail;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Util;

class SubscriptionPaymentFailed extends Mailable implements ShouldQueue
{
    use Mail, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->theme = $this->theme();

        $this->beforeSendMailMessage();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mixpost-enterprise::error.payment_failed_desc', ['app_name' => Util::appName(), 'workspace' => $this->subscription->workspace->name]),
        );
    }

    public function content(): Content
    {
        $this->beforeSendMailMessage();

        return new Content(
            markdown: 'mixpost-enterprise::mail.subscription-payment-failed',
            with: [
                'subscription' => $this->subscription,
                'workspace' => $this->subscription->workspace,
                'url' => route('mixpost_e.workspace.billing', [
                    'workspace' => $this->subscription->workspace->uuid,
                ]),
            ]
        );
    }
}
