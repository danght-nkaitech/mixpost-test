<?php

namespace Inovector\Mixpost;

use Inovector\Mixpost\Events\Account\AccountAdded;
use Inovector\Mixpost\Events\Account\AccountDeleted;
use Inovector\Mixpost\Events\Account\AccountUpdated;
use Inovector\Mixpost\Events\Post\PostDeleted;
use Inovector\Mixpost\Events\Post\PostPublished;
use Inovector\Mixpost\Events\Post\PostPublishedFailed;
use Inovector\Mixpost\Events\Post\PostScheduled;

class WebhookManager
{
    protected static array $customSystemEvents = [];

    protected static array $customWorkspaceEvents = [];

    public static function systemEvents(): array
    {
        return array_merge([], self::$customSystemEvents);
    }

    public static function workspaceEvents(): array
    {
        return array_merge(
            [
                AccountAdded::class,
                AccountUpdated::class,
                AccountDeleted::class,
                PostDeleted::class,
                PostScheduled::class,
                PostPublished::class,
                PostPublishedFailed::class,
            ],
            self::$customWorkspaceEvents,
        );
    }

    public static function getSystemEventSelectionOptions(): array
    {
        return self::getEventSelectionOptions(self::systemEvents());
    }

    public static function getSystemSelectionOptionKeys(): array
    {
        return array_keys(self::getSystemEventSelectionOptions());
    }

    public static function getWorkspaceEventSelectionOptions(): array
    {
        return self::getEventSelectionOptions(self::workspaceEvents());
    }

    public static function getWorkspaceSelectionOptionKeys(): array
    {
        return array_keys(self::getWorkspaceEventSelectionOptions());
    }

    private static function getEventSelectionOptions(array $events): array
    {
        return array_reduce($events, function ($array, $event) {
            $array[$event::name()] = $event::nameLocalized();

            return $array;
        }, []);
    }

    public static function addSystemEvent(string|array $event): void
    {
        if (is_array($event)) {
            self::$customSystemEvents = array_merge(self::$customSystemEvents, $event);

            return;
        }

        self::$customSystemEvents[] = $event;
    }

    public static function addWorkspaceEvent(string|array $event): void
    {
        if (is_array($event)) {
            self::$customWorkspaceEvents = array_merge(self::$customWorkspaceEvents, $event);

            return;
        }

        self::$customWorkspaceEvents[] = $event;
    }
}
