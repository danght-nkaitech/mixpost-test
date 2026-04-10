<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\MediaUploadFile;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;

class MediaUploadFileController extends Controller
{
    public function __invoke(MediaUploadFile $upload): MediaResource
    {
        return new MediaResource($upload->handle());
    }
}
