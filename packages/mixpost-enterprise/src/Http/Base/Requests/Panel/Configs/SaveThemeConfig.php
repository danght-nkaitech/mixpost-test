<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Facades\Theme;

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
