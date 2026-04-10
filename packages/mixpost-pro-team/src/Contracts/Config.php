<?php

namespace Inovector\Mixpost\Contracts;

interface Config
{
    public function group(): string;

    public function form(): array;

    public function rules(): array;

    public function messages(): array;
}
