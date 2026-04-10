<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\Concerns;

use LogicException;

trait ManagesReceipts
{
    public function getReceiptUrl(string $id): string
    {
        $url = $this->makeApiCall('get', "/transactions/$id/invoice")['data']['url'] ?? null;

        if (! $url) {
            throw new LogicException('The transaction does not have an invoice PDF.');
        }

        return $url;
    }
}
