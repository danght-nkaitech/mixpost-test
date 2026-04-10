<?php

namespace Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling;

use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    const SIGNATURE_KEY = 'Paddle-Signature';

    protected static int $maximumVariance = 5;

    public static function handle(Request $request, string $webhookSecret): bool
    {
        $signature = $request->header(self::SIGNATURE_KEY);

        if (self::isInvalidSignature($request, $signature, $webhookSecret)) {
            return false;
        }

        return true;
    }

    protected static function isInvalidSignature(Request $request, string $signature, string $webhookSecret): bool
    {
        if (empty($signature)) {
            return true;
        }

        [$timestamp, $hashes] = self::parseSignature($signature);

        if (self::$maximumVariance > 0 && time() > $timestamp + self::$maximumVariance) {
            return true;
        }

        $data = $request->getContent();

        foreach ($hashes as $hashAlgorithm => $possibleHashes) {
            $hash = match ($hashAlgorithm) {
                'h1' => hash_hmac('sha256', "{$timestamp}:{$data}", $webhookSecret),
            };

            foreach ($possibleHashes as $possibleHash) {
                if (hash_equals($hash, $possibleHash)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected static function parseSignature(string $header): array
    {
        $components = [
            'ts' => 0,
            'hashes' => [],
        ];

        foreach (explode(';', $header) as $part) {
            if (str_contains($part, '=')) {
                [$key, $value] = explode('=', $part, 2);

                match ($key) {
                    'ts' => $components['ts'] = (int) $value,
                    'h1' => $components['hashes']['h1'][] = $value,
                };
            }
        }

        return [
            $components['ts'],
            $components['hashes'],
        ];
    }
}
