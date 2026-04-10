<?php

namespace Inovector\MixpostEnterprise\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentPlatformWebhookHandler
{
    public function verifyWebhookSignature(Request $request): bool;

    public function handleWebhook(Request $request): Response;
}
