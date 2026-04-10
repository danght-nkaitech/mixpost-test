<?php

namespace Inovector\MixpostEnterprise\Events\User;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;

class UserEmailVerified implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public static function name(): string
    {
        return 'user.email_verified';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.user.email_verified');
    }

    public function payload(): array
    {
        return [
            'user' => new UserResource($this->user),
        ];
    }
}
