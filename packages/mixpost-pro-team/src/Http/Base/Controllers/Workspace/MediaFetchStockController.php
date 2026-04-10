<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Inovector\Mixpost\Configs\MediaConfig;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;
use Inovector\Mixpost\Integrations\Pexels\Pexels;
use Inovector\Mixpost\Integrations\Unsplash\Unsplash;
use Inovector\Mixpost\Models\Media;

class MediaFetchStockController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $method = 'fetch'.Str::studly(app(MediaConfig::class)->get('stock_photo_provider')).'Media';

        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        throw new \InvalidArgumentException('Invalid photo stock config');
    }

    private function fetchPexelsMedia(Request $request): AnonymousResourceCollection
    {
        $pexels = new Pexels;

        $items = $pexels->photos($request->query('keyword', ''), $request->query('page', 1));

        $media = collect($items)->map(function ($item) {
            $media = new Media([
                'name' => $item['photographer'],
                'mime_type' => 'image/jpeg',
                'disk' => 'external_media',
                'path' => $item['src']['original'],
                'conversions' => [
                    [
                        'disk' => 'stock',
                        'name' => 'thumb',
                        'path' => $item['src']['large'],
                    ],
                ],
            ]);

            $media->setAttribute('id', $item['id']);
            $media->setAttribute('source_url', 'https://pexels.com');
            $media->setAttribute('credit_url', $item['url']);
            $media->setAttribute('download_data', [
                'download_location' => '',
            ]);
            $media->setAttribute('data', [
                'source' => 'Pexels',
                'author' => $item['photographer'],
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

    private function fetchUnsplashMedia(Request $request): AnonymousResourceCollection
    {
        $unsplash = new Unsplash;

        $items = $unsplash->photos($request->query('keyword', ''), $request->query('page', 1));

        $media = collect($items)->map(function ($item) {
            $media = new Media([
                'name' => $item['user']['name'],
                'mime_type' => 'image/jpeg',
                'disk' => 'external_media',
                'path' => $item['urls']['regular'],
                'conversions' => [
                    [
                        'disk' => 'stock',
                        'name' => 'thumb',
                        'path' => $item['urls']['thumb'],
                    ],
                ],
            ]);

            $utmParams = http_build_query([
                'utm_source' => Config::get('app.name'),
                'utm_medium' => 'referral',
            ], '', '&');

            $media->setAttribute('id', $item['id']);
            $media->setAttribute('source_url', 'https://unsplash.com/'.'?'.$utmParams);
            $media->setAttribute('credit_url', $item['user']['links']['html'].'?'.$utmParams);
            $media->setAttribute('download_data', [
                'download_location' => $item['links']['download_location'],
            ]);
            $media->setAttribute('data', [
                'source' => 'Unsplash',
                'author' => $item['user']['name'],
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
