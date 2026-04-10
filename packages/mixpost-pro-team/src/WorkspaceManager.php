<?php

namespace Inovector\Mixpost;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Models\Workspace;

class WorkspaceManager
{
    use UsesWorkspaceModel;

    private ?Workspace $workspace = null;

    public function setCurrent(Workspace $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }

    public function forgetCurrent(): void
    {
        $this->workspace = null;
    }

    public function current(): ?Workspace
    {
        return $this->workspace;
    }

    public function findById(int $id): ?Workspace
    {
        return self::getWorkspaceModelClass()::find($id);
    }

    public function findByUuid(string $uuid): ?Workspace
    {
        return self::getWorkspaceModelClass()::findByUuid($uuid);
    }

    public function all(?Closure $callback = null): Collection|array
    {
        $query = self::getWorkspaceModelClass()::query();

        if ($callback) {
            call_user_func($callback, $query);
        }

        return $query->get();
    }

    public function loadById(int $id): bool
    {
        $workspace = $this->findById($id);

        if (! $workspace) {
            return false;
        }

        $this->setCurrent($workspace);

        return true;
    }

    public function loadByUuid(string $uuid): bool
    {
        $workspace = $this->findByUuid($uuid);

        if (! $workspace) {
            return false;
        }

        $this->setCurrent($workspace);

        return true;
    }

    public function uniqueRule($table, $column = 'NULL', $softDelete = false)
    {
        $query = (new Unique($table, $column))->where('workspace_id', $this->workspace->id);

        if ($softDelete) {
            $query = $query->whereNull('deleted_at');
        }

        return $query;
    }

    public function existsRule($table, $column = 'NULL', $softDelete = false)
    {
        $query = (new Exists($table, $column))->where('workspace_id', $this->workspace->id);

        if ($softDelete) {
            $query = $query->whereNull('deleted_at');
        }

        return $query;
    }
}
