<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\Paddle;

use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    const SIGNATURE_KEY = 'p_signature';

    public static function handle(Request $request, string $publicKey): bool
    {
        $fields = self::extractFields($request);
        $signature = $request->get(self::SIGNATURE_KEY);

        if (self::isInvalidSignature($fields, $signature, $publicKey)) {
            return false;
        }

        return true;
    }

    protected static function extractFields(Request $request): array
    {
        $fields = $request->except(self::SIGNATURE_KEY);

        ksort($fields);

        foreach ($fields as $k => $v) {
            if (! in_array(gettype($v), ['object', 'array'])) {
                $fields[$k] = "$v";
            }
        }

        return $fields;
    }

    protected static function isInvalidSignature(array $fields, $signature, $publicKey): bool
    {
        return openssl_verify(
            serialize($fields),
            base64_decode($signature),
            openssl_get_publickey($publicKey),
            OPENSSL_ALGO_SHA1
        ) !== 1;
    }
}
