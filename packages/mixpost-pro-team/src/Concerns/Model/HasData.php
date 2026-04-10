<?php

namespace Inovector\Mixpost\Concerns\Model;

use Illuminate\Support\Arr;

trait HasData
{
    public function getData(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }
}
