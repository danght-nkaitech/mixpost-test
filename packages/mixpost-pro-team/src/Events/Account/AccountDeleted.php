<?php

namespace Inovector\Mixpost\Events\Account;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;

class AccountDeleted implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public string $accountUuid;

    public function __construct(string $accountUuid)
    {
        $this->accountUuid = $accountUuid;
    }

    public static function name(): string
    {
        return 'account.deleted';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost::webhook.event.account.deleted');
    }

    public function payload(): array
    {
        return [
            'uuid' => $this->accountUuid,
        ];
    }
}
