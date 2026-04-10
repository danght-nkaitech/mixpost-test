<?php

namespace Inovector\MixpostEnterprise\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface Metric
{
    public function calculate(Request $request): Collection;
}
