<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Bluesky;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Services\Bluesky\Crypt\PrivateKey;

class GenerateBlueskyPrivateKeyController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $private = PrivateKey::create()->privateB64();

        return response()->json([
            'private_key' => $private,
        ]);
    }
}
