<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Builders\Media\MediaQuery;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;

class MediaFetchUploadsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $records = MediaQuery::apply($request)->latest()->simplePaginate(30);

        return MediaResource::collection($records);
    }
}
