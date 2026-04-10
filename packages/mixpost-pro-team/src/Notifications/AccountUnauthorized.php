<?php

namespace Inovector\Mixpost\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Concerns\Mail;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Models\Account;

class AccountUnauthorized extends Notification implements QueueWorkspaceAware, ShouldQueue
{
    use Mail, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public readonly Account $account) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->account->workspace !== null;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(
            route('mixpost.accounts.index', [
                'workspace' => $this->account->workspace->uuid,
            ], false)
        );

        return $this->mailNotificationMessage()
            ->subject(__('mixpost::account.backend.notification.unauthorized.subject'))
            ->line(__('mixpost::account.backend.notification.unauthorized.line1', ['name' => $this->account->name, 'provider' => $this->account->providerName()]))
            ->line(__('mixpost::account.backend.notification.unauthorized.line2'))
            ->line(__('mixpost::account.backend.notification.unauthorized.line3'))
            ->action(__('mixpost::account.accounts'), $url);
    }
}
