<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\MediaDownloadExternal;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;

class MediaDownloadExternalController extends Controller
{
    public function __invoke(MediaDownloadExternal $downloadMedia): array
    {
        $media = $downloadMedia->handle();

        return MediaResource::collection($media)->resolve();
    }
}
