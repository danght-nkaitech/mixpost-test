<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Main\ExtractUrlMeta;

class ExtractUrlMetaController extends Controller
{
    public function __invoke(ExtractUrlMeta $extractURLMeta): JsonResponse
    {
        return response()->json($extractURLMeta->handle());
    }
}
