<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Receipts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Models\Receipt;

class DeleteReceiptsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'success' => Receipt::whereIn('uuid', $request->input('receipts', []))->delete(),
        ]);
    }
}
