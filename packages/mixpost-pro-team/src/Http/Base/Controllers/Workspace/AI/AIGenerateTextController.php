<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\AI;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\AI\AIGenerateText;

class AIGenerateTextController extends Controller
{
    public function __invoke(AIGenerateText $generateText): JsonResponse
    {
        return response()->json([
            'text' => $generateText->handle(),
        ]);
    }
}
