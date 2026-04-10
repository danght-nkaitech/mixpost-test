<?php

namespace Inovector\MixpostEnterprise\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;

class PlanLimits implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return [];
        }

        return json_decode($value, true);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        $array = Arr::map(Arr::wrap($value), function ($item) {
            return [
                'code' => $item['code'],
                'form' => Arr::map($item['form'], function ($field) {
                    return [
                        'name' => $field['name'],
                        'value' => $field['value'],
                    ];
                }),
            ];
        });

        return json_encode($array);
    }
}
