<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Media\ChunkedUploadAbort;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Media\ChunkedUploadChunk;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Media\ChunkedUploadComplete;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Media\ChunkedUploadInitiate;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;

class MediaChunkedUploadController extends Controller
{
    public function initiate(ChunkedUploadInitiate $request): JsonResponse
    {
        return response()->json($request->handle());
    }

    public function upload(ChunkedUploadChunk $request): JsonResponse
    {
        return response()->json($request->handle());
    }

    public function complete(ChunkedUploadComplete $request): MediaResource
    {
        return new MediaResource($request->handle());
    }

    public function abort(ChunkedUploadAbort $request): JsonResponse
    {
        $request->handle();

        return response()->json(['success' => true]);
    }
}
