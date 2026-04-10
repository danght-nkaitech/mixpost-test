<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\MixpostEnterprise\Util;

class SubscriptionInfoResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'status' => $this->status->value,
            'email' => $this->email,
            'payment_method' => $this->paymentMethod,
            'card_brand' => $this->cardBrand,
            'card_last_four_digits' => $this->cardLastFourDigits,
            'card_expiration_date' => $this->cardExpirationDate,
            'last_payment' => $this->lastPayment ? [
                'amount' => $this->lastPayment->amount(),
                'currency' => $this->lastPayment->currency(),
                'date' => Util::dateFormat($this->lastPayment->date()),
            ] : null,
            'next_payment' => $this->nextPayment ? [
                'amount' => $this->nextPayment->amount(),
                'currency' => $this->nextPayment->currency(),
                'date' => Util::dateFormat($this->nextPayment->date()),
            ] : null,
        ];
    }
}
