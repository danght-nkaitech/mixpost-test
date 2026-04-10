<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Concerns\Makeable;

final readonly class DidDocument
{
    use Makeable;

    protected Collection $didDoc;

    public function __construct(array|Collection|null $didDoc = null)
    {
        $this->didDoc = Collection::wrap($didDoc)
            ->only([
                'service',
            ]);
    }

    public function pdsServiceEndpoint(string $default = ''): string
    {
        $service = collect((array) $this->didDoc->get('service', []))
            ->firstWhere('id', '#atproto_pds');

        return Arr::get($service, 'serviceEndpoint', $default);
    }
}
