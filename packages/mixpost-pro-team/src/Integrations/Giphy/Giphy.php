<?php

namespace Inovector\Mixpost\Integrations\Giphy;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Inovector\Mixpost\Services\GiphyService;
use Inovector\Mixpost\Util;

class Giphy
{
    protected string $clientId;

    private string $endpointUrl = 'https://api.giphy.com';

    private string $version = 'v1';

    public function __construct()
    {
        $clientId = GiphyService::getConfiguration('client_id');

        if (! $clientId) {
            throw new \Exception('Giphy is not configured.');
        }

        $this->clientId = $clientId;
    }

    public function gifs(string $query = '', int $page = 1): array
    {
        $limit = 30;
        $offset = ($page - 1) * $limit;

        return Http::get("$this->endpointUrl/$this->version/gifs/search", [
            'api_key' => $this->clientId,
            'q' => $query ?: Arr::random(Util::config('external_media_terms')),
            'limit' => $limit,
            'offset' => $offset,
        ])->json('data', []);
    }
}
