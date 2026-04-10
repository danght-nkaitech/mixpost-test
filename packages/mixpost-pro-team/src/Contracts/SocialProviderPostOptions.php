<?php

namespace Inovector\Mixpost\Contracts;

use Illuminate\Foundation\Http\FormRequest;

interface SocialProviderPostOptions
{
    public function rules(FormRequest $request): array;

    public function map(array $options = []): array;
}
