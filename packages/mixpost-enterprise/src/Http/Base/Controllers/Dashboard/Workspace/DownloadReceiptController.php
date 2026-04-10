<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Actions\Receipt\DownloadReceipt;

class DownloadReceiptController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $receipt = WorkspaceManager::current()
            ->receipts()
            ->where('uuid', $request->route('receipt'))
            ->firstOrFail();

        if ($receipt->hasUrl()) {
            abort(404);
        }

        if ($receipt->supportReceiptUrl()) {
            return $receipt->redirectToPdf();
        }

        return (new DownloadReceipt)($receipt);
    }
}
