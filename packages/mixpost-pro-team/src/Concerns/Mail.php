<?php

namespace Inovector\Mixpost\Concerns;

use Illuminate\Notifications\Messages\MailMessage;
use Inovector\Mixpost\Facades\HooksManager;
use Inovector\Mixpost\Hooks;

trait Mail
{
    public function theme(): string
    {
        return 'mixpost::mail.themes.default';
    }

    public function mailMessage(): MailMessage
    {
        $this->beforeSendMailMessage();

        return (new MailMessage)
            ->theme($this->theme());
    }

    public function mailNotificationMessage(): MailMessage
    {
        return $this->mailMessage()
            ->template('mixpost::mail.notification');
    }

    public function beforeSendMailMessage(): void
    {
        HooksManager::doAction(Hooks::ACTION_BEFORE_SEND_MAIL_MESSAGE, $this);
    }
}
