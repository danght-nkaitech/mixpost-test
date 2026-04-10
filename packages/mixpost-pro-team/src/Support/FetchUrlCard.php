<?php

namespace Inovector\Mixpost\Support;

use Closure;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;

class FetchUrlCard
{
    public function __invoke(string $url): array
    {
        $data = [
            'url' => $url,
            'title' => '',
            'description' => '',
            'image' => '',
        ];

        $twitterData = $data;

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate',
                ])
                ->get($url);

            if (! $response->successful()) {
                return [
                    'default' => $data,
                    'twitter' => $twitterData,
                ];
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($response->body());

            $xpath = new DOMXPath($doc);

            $data['title'] = $this->getAttributeContent($xpath, 'property', 'og:title', function () use ($xpath) {
                return $xpath->query('//title')->item(0)?->nodeValue;
            });
            $data['description'] = $this->getAttributeContent($xpath, 'property', 'og:description', '//meta[@name="description"]');
            $data['image'] = $this->getAttributeContent($xpath, 'property', 'og:image', '//img');

            $twitterData = [
                'url' => $this->getAttributeContent($xpath, 'name', 'twitter:url') ?: $url,
                'title' => $this->getAttributeContent($xpath, 'name', 'twitter:title') ?: $data['title'],
                'description' => $this->getAttributeContent($xpath, 'name', 'twitter:description') ?: $data['description'],
                'image' => $this->getAttributeContent($xpath, 'name', 'twitter:image') ?: $data['image'],
            ];

        } catch (Exception $e) {
            // Return the default data structure on error
        }

        return [
            'default' => $data,
            'twitter' => $twitterData,
        ];
    }

    private function getAttributeContent($xpath, $attribute, $attributeValue, Closure|string $fallbackQuery = '')
    {
        $node = $xpath->query('//meta[@'.$attribute.'="'.$attributeValue.'"]')->item(0);

        if ($node) {
            return $node->getAttribute('content');
        } elseif ($fallbackQuery) {
            if (is_callable($fallbackQuery)) {
                return $fallbackQuery();
            }

            $fallbackNode = $xpath->query($fallbackQuery)->item(0);

            if ($fallbackNode) {
                // For img tags, get 'src' attribute; for meta tags, get 'content' attribute
                if ($fallbackNode->nodeName === 'img') {
                    return $fallbackNode->getAttribute('src') ?: '';
                }

                return $fallbackNode->getAttribute('content') ?: '';
            }
        }

        return '';
    }
}
