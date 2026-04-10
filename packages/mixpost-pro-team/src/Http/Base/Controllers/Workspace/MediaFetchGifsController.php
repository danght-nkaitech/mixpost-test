<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inovector\Mixpost\Configs\GifConfig;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;
use Inovector\Mixpost\Integrations\Giphy\Giphy;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Services\TenorService;

class MediaFetchGifsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $method = 'fetch'.Str::studly(app(GifConfig::class)->get('gif_provider')).'Media';

        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        throw new \InvalidArgumentException('Invalid GIF provider config');
    }

    private function fetchTenorMedia(Request $request): AnonymousResourceCollection
    {
        $clientId = TenorService::getConfiguration('client_id');

        if (! $clientId) {
            abort(403);
        }

        $terms = config('mixpost.external_media_terms');

        $items = Http::get('https://tenor.googleapis.com/v2/search', [
            'key' => $clientId,
            'client_key' => Str::slug(Config::get('app.name', 'mixpost'), '_'),
            'q' => $request->query('keyword', Arr::random($terms)),
            'limit' => 30,
        ]);

        $media = collect($items->json('results', []))->map(function ($item) {
            $media = new Media([
                'name' => $item['content_description'],
                'mime_type' => 'image/gif',
                'disk' => 'external_media',
                'path' => $item['media_formats']['tinygif']['url'],
                'conversions' => [
                    [
                        'disk' => 'stock',
                        'name' => 'thumb',
                        'path' => $item['media_formats']['tinygif']['url'],
                    ],
                ],
            ]);

            $media->setAttribute('id', $item['id']);
            $media->setAttribute('source_url', 'https://tenor.com');
            $media->setAttribute('credit_url', $item['url'] ?? '');
            $media->setAttribute('download_data', 'false');
            $media->setAttribute('data', [
                'source' => 'Tenor',
                'author' => $item['content_description'],
            ]);

            return $media;
        });

        $nextPage = intval($request->get('page', 1)) + 1;

        return MediaResource::collection($media)->additional([
            'links' => [
                'next' => "?page=$nextPage",
            ],
        ]);
    }

    private function fetchGiphyMedia(Request $request): AnonymousResourceCollection
    {
        $giphy = new Giphy;

        $items = $giphy->gifs($request->query('keyword', ''), $request->query('page', 1));

        $media = collect($items)->map(function ($item) {
            $media = new Media([
                'name' => $item['title'],
                'mime_type' => 'image/gif',
                'disk' => 'external_media',
                'path' => $item['images']['original']['url'],
                'conversions' => [
                    [
                        'disk' => 'stock',
                        'name' => 'thumb',
                        'path' => $item['images']['fixed_height']['url'],
                    ],
                ],
            ]);

            $media->setAttribute('id', $item['id']);
            $media->setAttribute('source_url', 'https://giphy.com');
            $media->setAttribute('credit_url', $item['url'] ?? '');
            $media->setAttribute('download_data', 'false');
            $media->setAttribute('data', [
                'source' => 'Giphy',
                'author' => $item['user']['display_name'] ?? $item['username'] ?? $item['title'],
            ]);

            return $media;
        });

        $nextPage = intval($request->get('page', 1)) + 1;

        return MediaResource::collection($media)->additional([
            'links' => [
                'next' => "?page=$nextPage",
            ],
        ]);
    }
}
