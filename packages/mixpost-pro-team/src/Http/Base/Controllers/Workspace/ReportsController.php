<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Reports;

class ReportsController extends Controller
{
    public function __invoke(Reports $reports): JsonResponse
    {
        return response()->json($reports->handle());
    }
}
