<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\ShortenUrls;

class UrlShortenerController extends Controller
{
    public function shortenUrls(ShortenUrls $request): JsonResponse
    {
        return response()->json($request->handle());
    }
}
