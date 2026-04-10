<?php

namespace Inovector\Mixpost\UrlShortenerProviders;

use Exception;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Contracts\UrlShortenerProvider;
use Inovector\Mixpost\Services\YourlsService;

class YourlsProvider implements UrlShortenerProvider
{
    public string $signature;

    public string $domainUrl;

    public function __construct()
    {
        $domainUrl = YourlsService::getConfiguration('domain_url');
        $signature = YourlsService::getConfiguration('signature');

        if (! $domainUrl || ! $signature) {
            throw new Exception('Yourls is not configured.');
        }

        $this->signature = $signature;
        $this->domainUrl = $domainUrl;
    }

    public static function name(): string
    {
        return 'yourls';
    }

    public static function nameLocalized(): string
    {
        return 'Yourls';
    }

    public function shortenUrl(string $originalUrl): array
    {
        $response = Http::get("$this->domainUrl/yourls-api.php", [
            'signature' => $this->signature,
            'action' => 'shorturl',
            'url' => $originalUrl,
            'format' => 'json',
        ]);

        // TODO: Handle unauthorized access
        //  Should have its unhappy path first and its happy path last.

        if ($response['statusCode'] === 200 || ! empty($response['shorturl'])) { // also handles the case when short_url exists in yourls db, but not in mixpost's db
            return [
                'status' => 'OK',
                'short_url' => $response['shorturl'],
            ];
        }

        return [
            'status' => $response->status(),
            'response' => $response->json(),
        ];
    }
}
