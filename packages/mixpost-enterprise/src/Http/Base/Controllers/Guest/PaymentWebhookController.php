<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Guest;

use Illuminate\Http\Request;
use Inovector\MixpostEnterprise\Exceptions\NoPaymentPlatformActiveException;
use Inovector\MixpostEnterprise\PaymentPlatform;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController
{
    public function __invoke(Request $request): Response
    {
        // TODO: get payment platform by name from url?
        try {
            $instance = PaymentPlatform::activePlatformInstance();

            if (! $instance->verifyWebhookSignature($request)) {
                return new Response(__('mixpost-enterprise::dashboard.invalid_webhook_signature'), Response::HTTP_FORBIDDEN);
            }

            return $instance->handleWebhook($request);
        } catch (NoPaymentPlatformActiveException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
