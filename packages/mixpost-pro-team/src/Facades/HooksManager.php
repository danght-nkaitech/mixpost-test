<?php

namespace Inovector\Mixpost\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string addAction(string $hook, callable $callback, int $priority = 10)
 * @method static void removeAction(string $hook, string $id, int $priority = 10)
 * @method static void doAction(string $hook, mixed ...$args)
 * @method static string addFilter(string $hook, callable $callback, int $priority = 10)
 * @method static void removeFilter(string $hook, string $id, int $priority = 10)
 * @method static mixed applyFilters(string $hook, mixed $value, mixed ...$args)
 *
 * @see \Inovector\Mixpost\HooksManager
 */
class HooksManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostHooksManager';
    }
}
