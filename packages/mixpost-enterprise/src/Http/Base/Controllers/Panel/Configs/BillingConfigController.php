<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel\Configs;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Configs\SaveBillingConfig;
use Inovector\MixpostEnterprise\Util;

class BillingConfigController extends Controller
{
    public function view(Request $request): Response
    {
        return Inertia::render('Panel/Configs/BillingConfig', [
            'configs' => (new BillingConfig($request))->all(),
            'currencies' => Util::currenciesForSelect(),
        ]);
    }

    public function update(SaveBillingConfig $saveBillingConfig): RedirectResponse
    {
        $saveBillingConfig->handle();

        return redirect()->back();
    }
}
