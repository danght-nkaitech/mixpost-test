<?php

namespace Inovector\Mixpost;

final class HooksManager
{
    private array $actions = [];

    private array $filters = [];

    private array $sorted = [];

    private int $nextId = 0;

    public function addAction(string $hook, callable $callback, int $priority = 10): string
    {
        $id = $this->nextId++;
        $this->actions[$hook][$priority][$id] = $callback;
        unset($this->sorted[$hook]['actions']);

        return (string) $id;
    }

    public function removeAction(string $hook, string $id, int $priority = 10): void
    {
        unset($this->actions[$hook][$priority][$id]);
    }

    public function doAction(string $hook, mixed ...$args): void
    {
        if (empty($this->actions[$hook])) {
            return;
        }

        if (! isset($this->sorted[$hook]['actions'])) {
            ksort($this->actions[$hook]);
            $this->sorted[$hook]['actions'] = true;
        }

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if ($callback(...$args) === false) {
                    return;
                }
            }
        }
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10): string
    {
        $id = $this->nextId++;
        $this->filters[$hook][$priority][$id] = $callback;
        unset($this->sorted[$hook]['filters']);

        return (string) $id;
    }

    public function removeFilter(string $hook, string $id, int $priority = 10): void
    {
        unset($this->filters[$hook][$priority][$id]);
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (empty($this->filters[$hook])) {
            return $value;
        }

        if (! isset($this->sorted[$hook]['filters'])) {
            ksort($this->filters[$hook]);
            $this->sorted[$hook]['filters'] = true;
        }

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $callback($value, ...$args);
            }
        }

        return $value;
    }
}
