<?php

namespace Inovector\Mixpost\Events\Post;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Contracts\WebhookEvent;

class PostDeleted implements WebhookEvent
{
    use Dispatchable, SerializesModels;

    public array $uuids;

    public bool $toTrash;

    public function __construct(array $uuids, bool $toTrash = false)
    {
        $this->uuids = $uuids;
        $this->toTrash = $toTrash;
    }

    public static function name(): string
    {
        return 'post.deleted';
    }

    public static function nameLocalized(): string
    {
        return __('mixpost::webhook.event.post.deleted');
    }

    public function payload(): array
    {
        $data = [
            'uuids' => $this->uuids,
        ];

        if (! $this->toTrash) {
            $data['deleted'] = true;
        }

        if ($this->toTrash) {
            $data['to_trash'] = true;
        }

        return $data;
    }
}
