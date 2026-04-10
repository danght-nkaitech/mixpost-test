<?php

namespace Inovector\MixpostEnterprise\Abstracts;

use Illuminate\Support\Arr;
use Inovector\MixpostEnterprise\Contracts\FeatureLimitResource as FeatureLimitResourceInterface;
use Inovector\MixpostEnterprise\Enums\FeatureLimitResponseStatus;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

abstract class FeatureLimitResource implements FeatureLimitResourceInterface
{
    public string $name = 'Feature limit';

    public string $description = '';

    protected array $values;

    public function response(FeatureLimitResponseStatus $status, ?string $message = null, ?string $tip = null): FeatureLimitResponse
    {
        return new FeatureLimitResponse($status, $message, $tip);
    }

    public function makePasses(): FeatureLimitResponse
    {
        return $this->response(FeatureLimitResponseStatus::PASSES);
    }

    public function makeFails(): FeatureLimitResponse
    {
        return $this->response(FeatureLimitResponseStatus::FAILS);
    }

    public function limits(array $limits): self
    {
        $values = collect($limits)->firstWhere('code', $this->getBasename());

        $this->values = Arr::get($values, 'form', []);

        return $this;
    }

    public function getValue(string $key)
    {
        $value = collect($this->values)->firstWhere('name', $key);

        return Arr::get($value, 'value');
    }

    public function renderForm(): array
    {
        return Arr::map($this->form(), function ($item) {
            return $item->jsonSerialize();
        });
    }

    public function render(): array
    {
        return [
            'code' => $this->getBasename(),
            'name' => $this->name,
            'description' => $this->description,
            'form' => $this->renderForm(),
        ];
    }

    protected function getBasename(): string
    {
        return basename(str_replace('\\', '/', get_class($this)));
    }
}
