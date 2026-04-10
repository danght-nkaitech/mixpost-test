<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesCacheKey;
use InvalidArgumentException;

class Identity
{
    use UsesCacheKey;

    protected const PLC_DIRECTORY = 'https://plc.directory';

    protected const DID_REGEX = '/^did:[a-z]+:[a-zA-Z0-9._:%-]*[a-zA-Z0-9._-]$/';

    public static function isDID(?string $did): bool
    {
        return preg_match(self::DID_REGEX, $did ?? '') === 1;
    }

    public function resolveDID(string $did): Response
    {
        if (! self::isDID($did)) {
            throw new InvalidArgumentException("The did '$did' is not a valid DID.");
        }

        $cacheKey = $this->resolveCacheKey("bsky:resolve-did:$did");

        if (Cache::has($cacheKey)) {
            return new Response(Http::response(Cache::get($cacheKey))->wait());
        }

        $url = match (true) {
            Str::startsWith($did, 'did:plc:') => Str::of(self::PLC_DIRECTORY)->rtrim('/')->__toString().'/'.$did,
            Str::startsWith($did, 'did:web:') => 'https://'.Str::of($did)->remove('did:web:')->__toString().'/.well-known/did.json',
            default => throw new InvalidArgumentException('Unsupported DID type'),
        };

        $response = Http::timeout(10)->withoutRedirecting()->get($url);

        if ($response->successful()) {
            Cache::put($cacheKey, $response->json(), Carbon::now()->addDay());
        }

        return $response;
    }
}
