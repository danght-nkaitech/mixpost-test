<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paystack;

use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public static function handle(Request $request, string $secretKey): bool
    {
        $signature = $request->header('x-paystack-signature');

        if (self::isInvalidSignature($request->getContent(), $signature, $secretKey)) {
            return false;
        }

        return true;
    }

    protected static function isInvalidSignature(string $body, string $signature, string $secretKey): bool
    {
        return $signature !== hash_hmac('sha512', $body, $secretKey);
    }
}
