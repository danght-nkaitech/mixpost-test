<?php

namespace Inovector\Mixpost\Integrations\Pexels;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Services\PexelsService;
use Inovector\Mixpost\Util;

class Pexels
{
    protected string $clientId;

    private string $endpointUrl = 'https://api.pexels.com';

    private string $version = 'v1';

    public function __construct()
    {
        $clientId = PexelsService::getConfiguration('client_id');

        if (! $clientId) {
            throw new \Exception('Pexels is not configured.');
        }

        $this->clientId = $clientId;
    }

    public function photos(string $query = '', int $page = 1): array
    {
        return Http::withHeaders(['Authorization' => $this->clientId])
            ->get("$this->endpointUrl/$this->version/search", [
                'query' => $query ?: Arr::random(Util::config('external_media_terms')),
                'page' => $page,
                'per_page' => 30,
            ])->json('photos', []);
    }
}
