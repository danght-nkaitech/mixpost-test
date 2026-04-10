<?php

namespace Inovector\MixpostEnterprise\Abstracts;

use Inovector\MixpostEnterprise\Contracts\PaymentPlatform as PaymentPlatformContract;
use Inovector\MixpostEnterprise\Contracts\PaymentPlatformSubscription;
use Inovector\MixpostEnterprise\Contracts\PaymentPlatformWebhookHandler;
use Inovector\MixpostEnterprise\Models\Receipt;
use Inovector\MixpostEnterprise\Models\Subscription;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\SubscriptionInfo;
use LogicException;

abstract class PaymentPlatform implements PaymentPlatformContract, PaymentPlatformSubscription, PaymentPlatformWebhookHandler
{
    protected array $credentials = [];

    protected array $options = [];

    public function setCredentials(array $value): void
    {
        $this->credentials = $value;
    }

    public function setOptions(array $value): void
    {
        $this->options = $value;
    }

    public function supportTrialing(): bool
    {
        return true;
    }

    public function supportCoupon(): bool
    {
        return true;
    }

    public function supportSwapSubscription(): bool
    {
        return true;
    }

    public function supportResumeSubscription(): bool
    {
        return false;
    }

    public function supportReceiptUrl(): bool
    {
        return false;
    }

    public function resumeSubscription(Subscription $subscription, array $options = []): SubscriptionInfo
    {
        throw new LogicException('This payment platform does not support resume subscriptions.');
    }

    public function getReceiptUrl(string $id): string
    {
        throw new LogicException('This payment platform does not support receipt URLs.');
    }

    protected function findWorkspace($workspaceUuid): ?Workspace
    {
        return Workspace::findByUuid($workspaceUuid);
    }

    protected function findSubscription(string $subscriptionId): ?Subscription
    {
        return Subscription::firstWhere('platform_subscription_id', $subscriptionId);
    }

    protected function receiptExists(string $invoiceNumber): bool
    {
        return Receipt::where('invoice_number', $invoiceNumber)->exists();
    }
}
