<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Resources\ReceiptResource;

class ReceiptsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $workspace = WorkspaceManager::current();

        return Inertia::render('Dashboard/Workspace/Receipts', [
            'receipts' => ReceiptResource::collection($workspace->receipts()->latest()->paginate(10)),
        ]);
    }
}
