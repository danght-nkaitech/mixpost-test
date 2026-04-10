<?php

namespace Inovector\MixpostEnterprise\Contracts;

use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

interface FeatureLimitResource
{
    public function form(): array;

    public function validator(?object $data = null): FeatureLimitResponse;
}
