<?php

namespace Inovector\Mixpost\Concerns\Model\Media;

use Inovector\Mixpost\Concerns\UsesImageManager;

trait HasImageData
{
    use UsesImageManager;

    public function imageHeight()
    {
        if (isset($this->data['height'])) {
            return $this->data['height'];
        }

        $height = $this->imageManager()->read($this->getFullPath())->height();
        $this->saveImageHeight($height);

        return $height;
    }

    public function imageWidth()
    {
        if (isset($this->data['width'])) {
            return $this->data['width'];
        }

        return $this->imageManager()->read($this->getFullPath())->width();
    }

    public function saveImageHeight(int $height): void
    {
        $this->data = array_merge($this->data, ['height' => $height]);
        $this->save();
    }

    public function saveImageWidth(int $width): void
    {
        $this->data = array_merge($this->data, ['width' => $width]);
        $this->save();
    }
}
