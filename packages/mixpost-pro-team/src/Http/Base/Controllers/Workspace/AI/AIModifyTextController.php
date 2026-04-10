<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\AI;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\AI\AIModifyText;

class AIModifyTextController extends Controller
{
    public function __invoke(AIModifyText $modifyText): JsonResponse
    {
        return response()->json([
            'text' => $modifyText->handle(),
        ]);
    }
}
