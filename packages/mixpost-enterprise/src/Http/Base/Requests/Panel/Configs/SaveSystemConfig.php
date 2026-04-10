<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Configs\SystemConfig;

class SaveSystemConfig extends FormRequest
{
    public function rules(): array
    {
        return $this->config()->rules();
    }

    public function messages(): array
    {
        return $this->config()->messages();
    }

    public function handle(): void
    {
        $this->config()->save();
    }

    private function config()
    {
        return app(SystemConfig::class);
    }
}
