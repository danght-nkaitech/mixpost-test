<?php

namespace Inovector\Mixpost\Services\Bluesky\Crypt;

use Inovector\Mixpost\Facades\ServiceManager;
use InvalidArgumentException;

class PrivateKey extends Keypair
{
    public const CURVE = 'secp256r1';

    public const ALG = 'ES256';

    public const MULTIBASE_PREFIX = "\x80\x24";

    public static function load(?string $key = null): static
    {
        if (empty($key)) {
            $key = ServiceManager::get('bluesky', 'configuration.private_key');
        }

        if (empty($key)) {
            throw new InvalidArgumentException('Private key not configured');
        }

        return parent::load($key);
    }
}
