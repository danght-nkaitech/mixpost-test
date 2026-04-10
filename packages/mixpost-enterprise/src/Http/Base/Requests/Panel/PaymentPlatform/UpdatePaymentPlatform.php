<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\PaymentPlatform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inovector\MixpostEnterprise\Contracts\PaymentPlatform as PaymentPlatformContract;
use Inovector\MixpostEnterprise\Models\PaymentPlatform as Model;
use Inovector\MixpostEnterprise\PaymentPlatform;

class UpdatePaymentPlatform extends FormRequest
{
    public function rules(): array
    {
        $default = [
            'enabled' => ['required', 'boolean'],
            'name' => ['required', Rule::in(PaymentPlatform::supported())],
        ];

        return array_merge(
            $default,
            $this->platformClass()::formRules()
        );
    }

    public function handle(): void
    {
        Model::updateOrCreate(['name' => $this->input('name')], [
            'credentials' => Arr::only($this->input('credentials', []),
                array_keys($this->platformClass()::formCredentials())
            ),
            'options' => Arr::only($this->input('options', []),
                array_keys($this->platformClass()::formOptions())
            ),
            'enabled' => $this->input('enabled'),
        ]);

        // Disable all payments except the current one.
        if ($this->input('enabled')) {
            Model::where('name', '<>', $this->input('name'))->update([
                'enabled' => false,
            ]);
        }
    }

    public function messages(): array
    {
        return $this->platformClass()::formMessages();
    }

    private function platformClass(): PaymentPlatformContract
    {
        return PaymentPlatform::getPlatformClassByName($this->input('name', ''));
    }
}
