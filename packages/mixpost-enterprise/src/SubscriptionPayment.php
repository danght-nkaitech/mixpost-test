<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class SubscriptionPayment implements Arrayable
{
    public function __construct(private readonly string $amount, private readonly string $currency, private readonly ?Carbon $date) {}

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function date(): ?Carbon
    {
        return $this->date;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount(),
            'currency' => $this->currency,
            'date' => $this->date()?->toIso8601String(),
        ];
    }
}
