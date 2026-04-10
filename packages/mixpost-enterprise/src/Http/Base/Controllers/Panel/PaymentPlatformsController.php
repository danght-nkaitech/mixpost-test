<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\PaymentPlatform\UpdatePaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatform;

class PaymentPlatformsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Panel/PaymentPlatforms/PaymentPlatforms', [
            'platforms' => PaymentPlatform::all(),
        ]);
    }

    public function update(UpdatePaymentPlatform $updatePaymentPlatform): RedirectResponse
    {
        $updatePaymentPlatform->handle();

        return back();
    }
}
