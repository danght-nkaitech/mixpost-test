<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Stripe;

use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;

class VerifyWebhookSignature
{
    public static function handle(Request $request, string $secret): bool
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $secret,
                300
            );

            return true;
        } catch (SignatureVerificationException $exception) {
            return false;
        }
    }
}
