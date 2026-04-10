<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Facades\UrlShortenerManager;
use Inovector\Mixpost\Models\ShortenedUrl;

class ShortenUrls extends FormRequest
{
    public function rules(): array
    {
        return [
            'urls' => ['required', 'array'],
            'urls.*' => ['required', 'string'],
            'url_shortener_active' => ['required', 'boolean'],
        ];
    }

    public function handle(): array
    {
        $incomingUrls = collect($this->input('urls'))->filter(fn ($url) => strlen($url) <= 256)->toArray();
        // TODO: Warn user about URLs longer than 256 characters - Maybe on frontend validation?
        $outgoingUrls = [];

        $targetUrlColumn = $this->input('url_shortener_active') ? 'original_url' : 'short_url';

        $savedUrls = ShortenedUrl::select('original_url', 'short_url')
            ->whereIn($targetUrlColumn, $incomingUrls)
            ->get();

        foreach ($savedUrls as $savedUrl) {
            $incomingUrls = array_filter($incomingUrls, fn ($incomingUrl) => $incomingUrl !== $savedUrl[$targetUrlColumn]);
            $outgoingUrls[] = [
                'original_url' => $savedUrl->original_url,
                'short_url' => $savedUrl->short_url,
            ];
        }

        if ($this->input('url_shortener_active')) {
            try {
                $connection = UrlShortenerManager::connect();
            } catch (Exception $e) {
                return [
                    'status' => 'ERROR',
                    'message' => $e->getMessage(),
                ];
            }

            foreach ($incomingUrls as $incomingUrl) {
                try {
                    $response = $connection->shortenUrl($incomingUrl);
                } catch (Exception $e) {
                    return [
                        'status' => 'ERROR',
                        'message' => __('mixpost::error.cannot_connect_service', ['service' => $connection->nameLocalized()]),
                    ];
                }

                if ($response['status'] === 'OK') {
                    $outgoingUrls[] = $shortenedUrl = [
                        'original_url' => $incomingUrl,
                        'short_url' => $response['short_url'],
                    ];

                    ShortenedUrl::create(array_merge($shortenedUrl, ['provider' => $connection->name()]));
                }
            }
        }

        return [
            'status' => 'OK',
            'urls' => $outgoingUrls,
        ];
    }
}
