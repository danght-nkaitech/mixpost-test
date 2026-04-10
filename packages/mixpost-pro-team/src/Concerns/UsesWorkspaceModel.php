<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Models\Workspace;

trait UsesWorkspaceModel
{
    public static function getWorkspaceModelClass(): string
    {
        $model = Mixpost::getCustomWorkspaceModel();

        if (! $model) {
            return Workspace::class;
        }

        return $model;
    }
}
