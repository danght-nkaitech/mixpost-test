<?php

namespace Inovector\Mixpost\SocialProviders\Bluesky\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OAuthSession
{
    protected Collection $session;

    final public function __construct(array|Collection|null $session = null)
    {
        $this->session = Collection::wrap($session);
    }

    public static function create(array|Collection|null $session = null): static
    {
        return new static($session);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->session, $key, $default);
    }

    public function put(string $key, mixed $value): static
    {
        $this->session->put($key, $value);

        return $this;
    }
}
