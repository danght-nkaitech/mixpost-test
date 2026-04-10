<?php

namespace Inovector\Mixpost\UrlShortenerProviders;

use Exception;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Contracts\UrlShortenerProvider;
use Inovector\Mixpost\Services\BitlyService;

class BitlyProvider implements UrlShortenerProvider
{
    public string $token;

    public string $apiVersion = 'v4';

    public string $apiUrl = 'https://api-ssl.bitly.com';

    public function __construct()
    {
        $token = BitlyService::getConfiguration('token');

        if (! $token) {
            throw new Exception('Bitly is not configured.');
        }

        $this->token = $token;
    }

    public static function name(): string
    {
        return 'bitly';
    }

    public static function nameLocalized(): string
    {
        return 'Bitly';
    }

    public function shortenUrl(string $originalUrl): array
    {
        $response = Http::withToken($this->token)
            ->post("$this->apiUrl/$this->apiVersion/shorten", ['long_url' => $originalUrl]);

        // TODO: Handle unauthorized access
        //  Should have its unhappy path first and its happy path last.

        if (in_array($response->status(), [200, 201])) {
            return [
                'status' => 'OK',
                'short_url' => $response->json('link'),
            ];
        }

        return [
            'status' => $response->status(),
            'response' => $response->json(),
        ];
    }
}
