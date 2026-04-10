<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\Theme;

class SaveThemeConfig extends FormRequest
{
    public function rules(): array
    {
        return Theme::config()->rules();
    }

    public function messages(): array
    {
        return Theme::config()->messages();
    }

    public function handle(): void
    {
        Theme::config()->save();
    }
}
