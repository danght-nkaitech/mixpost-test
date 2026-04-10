<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Configs\MediaConfig;

class SaveMediaConfig extends FormRequest
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
        return app(MediaConfig::class);
    }
}
