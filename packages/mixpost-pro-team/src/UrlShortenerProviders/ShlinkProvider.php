<?php

namespace Inovector\Mixpost\UrlShortenerProviders;

use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Contracts\UrlShortenerProvider;
use Inovector\Mixpost\Services\ShlinkService;

class ShlinkProvider implements UrlShortenerProvider
{
    public string $apiKey;

    public string $domainUrl;

    public string $apiVersion = 'v3';

    public function __construct()
    {
        $domainUrl = ShlinkService::getConfiguration('domain_url');
        $apiKey = ShlinkService::getConfiguration('api_key');

        if (! $domainUrl || ! $apiKey) {
            throw new \Exception('Shlink is not configured.');
        }

        $this->apiKey = $apiKey;
        $this->domainUrl = $domainUrl;
    }

    public static function name(): string
    {
        return 'shlink';
    }

    public static function nameLocalized(): string
    {
        return 'Shlink';
    }

    public function shortenUrl(string $originalUrl): array
    {
        $response = Http::withHeader('X-Api-Key', $this->apiKey)
            ->post("$this->domainUrl/rest/$this->apiVersion/short-urls", [
                'longUrl' => $originalUrl,
            ]);

        // TODO: Handle unauthorized access
        //  Should have its unhappy path first and its happy path last.

        if ($response->status() === 200) {
            return [
                'status' => 'OK',
                'short_url' => $response['shortUrl'],
            ];
        }

        return [
            'status' => $response->status(),
            'response' => $response->json(),
        ];
    }
}
