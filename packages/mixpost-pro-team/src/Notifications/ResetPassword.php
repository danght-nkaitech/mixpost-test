<?php

namespace Inovector\Mixpost\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Inovector\Mixpost\Concerns\Mail;

class ResetPassword extends Notification implements ShouldQueue
{
    use Mail, Queueable;

    public string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(
            route('mixpost.password.reset', [
                'token' => $this->token,
            ], false)
        );

        return $this->mailNotificationMessage()
            ->subject(__('mixpost::auth.backend.notification.reset_password_notification'))
            ->line(__('mixpost::auth.backend.notification.password_reset_request_received'))
            ->action(__('mixpost::auth.reset_password'), $url)
            ->line(__('mixpost::auth.backend.notification.password_reset_link_expiry', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(__('mixpost::auth.backend.notification.no_password_reset_required'));
    }
}
