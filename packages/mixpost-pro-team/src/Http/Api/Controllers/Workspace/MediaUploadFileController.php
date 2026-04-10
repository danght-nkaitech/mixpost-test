<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace;

use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Requests\Workspace\MediaUploadFile;
use Inovector\Mixpost\Http\Api\Resources\MediaResource;

class MediaUploadFileController extends Controller
{
    public function __invoke(MediaUploadFile $upload): MediaResource
    {
        return new MediaResource($upload->handle());
    }
}
