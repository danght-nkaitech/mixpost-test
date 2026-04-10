<?php

namespace Inovector\MixpostEnterprise\Events\User;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Contracts\WebhookEvent;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;

class UserCreated implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public User $user;

    public bool $fromAdmin;

    public function __construct(User $user, bool $fromAdmin = false)
    {
        $this->user = $user;
        $this->fromAdmin = $fromAdmin;
    }

    public static function name(): string
    {
        return 'user.created';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost-enterprise::webhook.event.user.created');
    }

    public function payload(): array
    {
        return [
            'user' => new UserResource($this->user),
            'from_admin' => $this->fromAdmin,
        ];
    }
}
