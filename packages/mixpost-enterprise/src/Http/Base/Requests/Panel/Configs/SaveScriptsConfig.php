<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Facades\ScriptsConfig;

class SaveScriptsConfig extends FormRequest
{
    public function rules(): array
    {
        return ScriptsConfig::rules();
    }

    public function messages(): array
    {
        return ScriptsConfig::messages();
    }

    public function handle(): void
    {
        ScriptsConfig::save();
    }
}
