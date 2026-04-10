<?php

namespace Inovector\Mixpost\Concerns;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;

trait UsesImageManager
{
    public function imageManager(?DriverInterface $driver = null): ImageManager
    {
        return new ImageManager($driver ?? new GdDriver);
    }
}
