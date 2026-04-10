<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Inovector\Mixpost\Http\Base\Requests\Admin\UploadFile;

class UploadFileController
{
    public function __invoke(UploadFile $uploadFile): JsonResponse
    {
        $file = $uploadFile->handle();

        return response()->json($file);
    }
}
