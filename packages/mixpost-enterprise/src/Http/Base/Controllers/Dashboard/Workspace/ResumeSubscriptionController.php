<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\ResumeSubscription;
use Inovector\MixpostEnterprise\PaymentPlatform;

class ResumeSubscriptionController extends Controller
{
    public function __invoke(ResumeSubscription $resumeSubscription): RedirectResponse
    {
        $platformInstance = PaymentPlatform::activePlatformInstance();

        if (! $platformInstance->supportResumeSubscription()) {
            return redirect()
                ->route('mixpost_e.workspace.billing', ['workspace' => WorkspaceManager::current()->uuid])
                ->with('warning', __('mixpost-enterprise::subscription.platform_not_support_resume'));
        }

        $resumeSubscription->handle();

        return redirect()
            ->route('mixpost_e.workspace.billing', ['workspace' => WorkspaceManager::current()->uuid])
            ->with('success', __('mixpost-enterprise::subscription.sub_resumed'));
    }
}
