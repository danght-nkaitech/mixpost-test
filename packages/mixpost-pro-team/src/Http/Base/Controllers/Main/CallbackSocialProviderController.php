<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Actions\Account\UpdateOrCreateAccount;
use Inovector\Mixpost\Exceptions\OAuthInvalidGrant;
use Inovector\Mixpost\Exceptions\OAuthSessionExpired;
use Inovector\Mixpost\Facades\SocialProviderManager;
use Inovector\Mixpost\Facades\WorkspaceManager;

class CallbackSocialProviderController extends Controller
{
    public function __invoke(Request $request, UpdateOrCreateAccount $updateOrCreateAccount, string $providerName): RedirectResponse
    {
        try {
            $provider = SocialProviderManager::connect($providerName);
        } catch (OAuthSessionExpired $e) {
            return $this->redirectToAccounts()->with('error', __('mixpost::error.page_expired'));
        } catch (OAuthInvalidGrant $e) {
            return $this->redirectToAccounts()->with('error', __('mixpost::error.invalid_grant'));
        }

        if (empty($provider->getCallbackResponse())) {
            return $this->redirectToAccounts();
        }

        if ($error = $request->get('error')) {
            return $this->redirectToAccounts()->with('error', $error);
        }

        if (! $provider->isOnlyUserAccount()) {
            return redirect()->route('mixpost.accounts.entities.index', ['workspace' => WorkspaceManager::current()->uuid, 'provider' => $providerName])
                ->with('mixpost_callback_response', $provider->getCallbackResponse());
        }

        $accessToken = $provider->requestAccessToken($provider->getCallbackResponse());

        if ($error = Arr::get($accessToken, 'error')) {
            return $this->redirectToAccounts()->with('error', $error);
        }

        $provider->setAccessToken($accessToken);

        $account = $provider->getAccount();

        if ($account->hasError()) {
            $message = $account->hasExceededRateLimit() ? $account->message : __('mixpost::error.try_again');

            return $this->redirectToAccounts()->with('error', $message);
        }

        $updateOrCreateAccount($providerName, $account->context(), $accessToken);

        return $this->redirectToAccounts();
    }

    protected function redirectToAccounts(): RedirectResponse
    {
        return redirect()->route('mixpost.accounts.index', ['workspace' => WorkspaceManager::current()->uuid]);
    }
}
