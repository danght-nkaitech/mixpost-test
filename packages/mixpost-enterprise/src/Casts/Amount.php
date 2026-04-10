<?php

namespace Inovector\MixpostEnterprise\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Amount implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value / 100;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value * 100;
    }
}
