<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Actions\Receipt\DownloadReceipt;
use Inovector\MixpostEnterprise\Models\Receipt;

class DownloadReceiptController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $receipt = Receipt::firstOrFailByUuid($request->route('receipt'));

        if ($receipt->supportReceiptUrl()) {
            return $receipt->redirectToPdf();
        }

        return (new DownloadReceipt)($receipt);
    }
}
