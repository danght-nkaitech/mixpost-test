<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Models\Receipt;

class DeleteReceiptsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Receipt::whereIn('uuid', $request->input('receipts', []))->delete();

        return redirect()
            ->back()
            ->with('success', __('mixpost-enterprise::finance.receipts_deleted'));
    }
}
