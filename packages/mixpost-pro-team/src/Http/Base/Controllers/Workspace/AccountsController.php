<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Actions\Account\UpdateOrCreateAccount;
use Inovector\Mixpost\Concerns\UsesSocialProviderManager;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Events\Account\AccountDeleted;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Http\Base\Resources\AccountResource;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\SocialProviders\Linkedin\LinkedinProvider;

class AccountsController extends Controller
{
    use UsesSocialProviderManager;

    public function index(): Response
    {
        $socialServices = ServiceManager::services()->group(ServiceGroup::SOCIAL)->getNames();

        $systemConfig = Mixpost::getEnterpriseConfig()['system'] ?? null;

        return Inertia::render('Workspace/Accounts/Accounts', [
            'accounts' => AccountResource::collection(Account::latest()->get())->resolve(),
            'is_configured_service' => ServiceManager::isConfigured($socialServices),
            'is_service_active' => ServiceManager::isActive($socialServices),
            'additionally' => [
                'linkedin_supports_pages' => LinkedinProvider::hasCommunityManagementProduct(),
            ],
            'enterprise_config' => [
                'workspace_twitter_service' => $systemConfig ? app($systemConfig)->allowWorkspaceTwitterService() : false,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $account = Account::firstOrFailByUuid($request->route('account'));

        if ($account->isUnauthorized()) {
            return redirect()->back()->with('error', __('mixpost::account.account_reauthenticate'));
        }

        $connection = $this->connectProvider($account);

        $response = $connection->getAccount();

        if ($response->hasError()) {
            if ($response->isUnauthorized()) {
                $account->setUnauthorized(false);

                return redirect()->back()->with('error', __('mixpost::account.account_reauthenticate'));
            }

            if ($response->hasExceededRateLimit()) {
                return redirect()->back()->with('error', $response->message);
            }

            return redirect()->back()->with('error', __('mixpost::account.account_not_updated'));
        }

        (new UpdateOrCreateAccount)($account->provider, $response->context(), $account->access_token->toArray());

        return redirect()->back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $account = Account::firstOrFailByUuid($request->route('account'));

        $connection = $this->connectProvider($account);

        if (method_exists($connection, 'revokeToken')) {
            $connection->revokeToken();
        }

        $account->delete();

        AccountDeleted::dispatch($account->uuid);

        return redirect()->back();
    }
}
