<?php

namespace Inovector\Mixpost\SocialProviders\Linkedin\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\SocialProviderContentType;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\FetchUrlCard;
use Inovector\Mixpost\Support\SocialProviderResponse;
use Inovector\Mixpost\Support\TemporaryFile;
use RuntimeException;

trait ManagesPost
{
    use ManageComments;
    use UsesUploads;

    public function publishPost(string $text, Collection $media, array $params = []): SocialProviderResponse
    {
        if ($this->hasRefreshToken() && $this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        try {
            $postParams = [
                'author' => "urn:li:{$this->author()}:{$this->values['provider_id']}",
                'commentary' => $this->escapeText($text),
                'visibility' => Str::upper(Arr::get($params, 'visibility', 'PUBLIC')),
                'distribution' => [
                    'feedDistribution' => 'MAIN_FEED',
                ],
                'lifecycleState' => 'PUBLISHED',
                'isReshareDisabledByAuthor' => false,
            ];

            /**
             * @var SocialProviderResponse $previousPostResponse
             */
            if ($previousPostResponse = $params['previous_post_response'] ?? null && self::contentType() === SocialProviderContentType::COMMENTS) {
                return $this->publishComment($text, $previousPostResponse->id());
            }

            $this->handleArticle($media, $params, $postParams);

            // One image post
            if ($media->count() === 1 && $media->first()->isImage()) {
                $this->handleImage($media->first(), $postParams);
            }

            // One video post
            if ($media->count() === 1 && $media->first()->isVideo()) {
                $this->handleVideo($media->first(), $params, $postParams);
            }

            // Multiple images post
            if ($media->count() > 1) {
                $this->handleImages($media->filter(fn ($media) => $media->isImage()), $postParams);
            }
        } catch (RuntimeException $e) {
            return $this->response(SocialProviderResponseStatus::ERROR, json_decode($e->getMessage(), true));
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeadersNext())
            ->post("$this->apiUrl/rest/posts", $postParams);

        return $this->buildResponse($response, function () use ($response) {
            return [
                'id' => $response->header('x-linkedin-id'),
            ];
        });
    }

    public function deletePost(string $id, array $params = []): SocialProviderResponse
    {
        if ($this->hasRefreshToken() && $this->tokenIsAboutToExpire()) {
            $newAccessToken = $this->refreshToken();

            if ($newAccessToken->hasError()) {
                return $newAccessToken;
            }

            $this->updateToken($newAccessToken->context());
        }

        $response = $this->getHttpClient()::withToken($this->getAccessToken()['access_token'])
            ->withHeaders($this->httpHeadersNext())
            ->delete("$this->apiUrl/rest/posts/$id");

        if ($response->notFound()) {
            /**
             * Handle 404 response when attempting to delete a post that no longer exists on the platform.
             * This occurs when we have a stored post_provider_id but the post has already been deleted directly on the platform.
             */
            return $this->response(SocialProviderResponseStatus::OK, []);
        }

        return $this->buildResponse($response);
    }

    private function handleArticle(Collection $media, array $params, array &$postParams): void
    {
        if ($media->count()) {
            return;
        }

        if (! $url = $params['url'] ?? '') {
            return;
        }

        $card = (new FetchUrlCard)($url);

        if (! $card['default']['title']) {
            return;
        }

        $postParams['content'] = [
            'article' => [
                'source' => $url,
                'title' => $card['default']['title'],
                'description' => $card['default']['description'] ?? '',
            ],
        ];

        if ($card['default']['image']) {
            $file = null;

            try {
                $file = TemporaryFile::make()->fromUrl($card['default']['image']);
                $response = $this->uploadImage($file);

                if (! $response->hasError()) {
                    $postParams['content']['article']['thumbnail'] = $response->id();
                }
            } finally {
                $file?->directory()->delete();
            }
        }
    }

    private function handleImage(Media $image, array &$postParams): void
    {
        $response = $this->uploadImage($image);

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        $postParams['content']['media'] = [
            'id' => $response->id(),
            'altText' => $image->alt_text ?? '',
        ];
    }

    private function handleVideo(Media $video, array $params, array &$postParams): void
    {
        $response = $this->uploadVideo($video, $params);

        if ($response->hasError()) {
            throw new RuntimeException(json_encode($response->context()));
        }

        $postParams['content']['media'] = [
            'id' => $response->id(),
        ];
    }

    private function handleImages(Collection $images, array &$postParams): void
    {
        $uploadedImages = $images->map(function ($media) {
            $response = $this->uploadImage($media);

            if ($response->hasError()) {
                throw new RuntimeException(json_encode($response->context()));
            }

            return [
                'id' => $response->id(),
                'altText' => $media->alt_text ?? '',
            ];
        });

        if ($uploadedImages->isEmpty()) {
            return;
        }

        $postParams['content']['multiImage']['images'] = $uploadedImages->toArray();
    }

    /**
     * Escape reserved characters in the text to prevent issues with LinkedIn's API.
     *
     * @see https://learn.microsoft.com/en-us/linkedin/marketing/community-management/shares/little-text-format?view=li-lms-2025-05&tabs=curl#text
     */
    private function escapeText(string $text): string
    {
        // Reserved characters that must be escaped according to LinkedIn's official documentation
        $reservedChars = [
            '\\' => '\\\\',  // Backslash must be escaped first to avoid double escaping
            '|' => '\\|',   // Pipe character
            '{' => '\\{',   // Opening curly brace
            '}' => '\\}',   // Closing curly brace
            '@' => '\\@',   // At symbol (used for mentions)
            '[' => '\\[',   // Opening square bracket
            ']' => '\\]',   // Closing square bracket
            '(' => '\\(',   // Opening parenthesis - this fixes your specific issue!
            ')' => '\\)',   // Closing parenthesis
            '<' => '\\<',   // Less than
            '>' => '\\>',   // Greater than
            '#' => '\\#',   // Hash symbol (used for hashtags)
            '*' => '\\*',   // Asterisk
            '_' => '\\_',   // Underscore
            '~' => '\\~',    // Tilde
        ];

        // Apply escaping in the correct order (backslash first!)
        foreach ($reservedChars as $char => $escaped) {
            $text = str_replace($char, $escaped, $text);
        }

        return $text;
    }
}
