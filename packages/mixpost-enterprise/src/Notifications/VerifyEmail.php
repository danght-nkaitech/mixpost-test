<?php

namespace Inovector\MixpostEnterprise\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Inovector\Mixpost\Concerns\Mail;

class VerifyEmail extends Notification implements ShouldQueue
{
    use Mail, Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->mailNotificationMessage()
            ->subject(__('mixpost-enterprise::onboarding.verify_email'))
            ->line(__('mixpost-enterprise::onboarding.click_to_verify'))
            ->action(__('mixpost-enterprise::onboarding.verify_email'), $this->verificationUrl($notifiable))
            ->line(__('mixpost-enterprise::onboarding.no_account_no_action'));
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'mixpost_e.emailVerification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
