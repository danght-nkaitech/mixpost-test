<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Public;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Facades\SocialProviderManager;

class UninstallCallbackSocialProviderController extends Controller
{
    public function __invoke(Request $request, string $providerName)
    {
        try {
            $connection = SocialProviderManager::connect($providerName);

            if (! method_exists($connection, 'handleUninstallRequest')) {
                return $this->redirectHome();
            }

            return $connection->handleUninstallRequest($request);
        } catch (Exception $e) {
            return $this->redirectHome();
        }
    }

    private function redirectHome(): RedirectResponse
    {
        return redirect()->route('mixpost.home', [], 301);
    }
}
