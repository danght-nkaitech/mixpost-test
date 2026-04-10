<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Configs\SystemConfig;
use Inovector\MixpostEnterprise\Facades\WorkspaceServiceManager;
use Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace\SaveService;

class ServicesController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard/Workspace/Services', [
            'services' => WorkspaceServiceManager::all(),
            'redirect_to_accounts' => $request->get('redirect-to-accounts', false),
            'config' => [
                'system' => [
                    'twitter_api_workspace_docs_url' => app(SystemConfig::class)->get('twitter_api_workspace_docs_url'),
                ],
            ],
        ]);
    }

    public function update(SaveService $saveService): RedirectResponse
    {
        $saveService->handle();

        return back();
    }
}
