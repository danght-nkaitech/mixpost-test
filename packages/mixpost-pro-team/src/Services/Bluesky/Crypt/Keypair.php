<?php

namespace Inovector\Mixpost\Services\Bluesky\Crypt;

use Firebase\JWT\JWT;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\EC\Formats\Keys\PKCS8;
use phpseclib3\Crypt\EC\PrivateKey;
use phpseclib3\Crypt\EC\PublicKey;

abstract class Keypair
{
    const CURVE = '';

    public const ALG = '';

    protected PrivateKey $key;

    final public function __construct() {}

    public static function load(string $key): static
    {
        $self = new static;

        /** @var \Inovector\Mixpost\Services\Bluesky\Crypt\PrivateKey $sk */
        $sk = EC::loadPrivateKey(JWT::urlsafeB64Decode($key));

        $self->key = $sk;

        return $self;
    }

    public static function create(): static
    {
        $self = new static;

        $self->key = EC::createKey(static::CURVE);

        return $self;
    }

    public function privateKey(): PrivateKey
    {
        return $this->key;
    }

    public function privatePEM(): string
    {
        return $this->key->toString(class_basename(PKCS8::class));
    }

    public function privateB64(): string
    {
        return JWT::urlsafeB64Encode($this->privatePEM());
    }

    public function publicKey(): PublicKey
    {
        return $this->key->getPublicKey();
    }

    public function publicPEM(): string
    {
        return $this->publicKey()->toString(class_basename(PKCS8::class));
    }

    public function toJWK(): JsonWebKey
    {
        return new JsonWebKey($this->privateKey());
    }
}
