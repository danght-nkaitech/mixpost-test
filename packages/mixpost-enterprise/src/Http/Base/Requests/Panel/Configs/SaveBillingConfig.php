<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Configs\BillingConfig;

class SaveBillingConfig extends FormRequest
{
    public BillingConfig $config;

    public function __construct()
    {
        parent::__construct();

        $this->config = new BillingConfig($this);
    }

    public function rules(): array
    {
        return $this->config->rules();
    }

    public function messages(): array
    {
        return $this->config->messages();
    }

    public function handle(): void
    {
        $this->config->save();
    }
}
