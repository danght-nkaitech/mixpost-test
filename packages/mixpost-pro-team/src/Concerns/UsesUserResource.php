<?php

namespace Inovector\Mixpost\Concerns;

use Inovector\Mixpost\Http\Base\Resources\UserResource;
use Inovector\Mixpost\Mixpost;

trait UsesUserResource
{
    public static function getUserResourceClass(): string
    {
        $resource = Mixpost::getCustomUserResource();

        if (! $resource) {
            return UserResource::class;
        }

        return $resource;
    }
}
