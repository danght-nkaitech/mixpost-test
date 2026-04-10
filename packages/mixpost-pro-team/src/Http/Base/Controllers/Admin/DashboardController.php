<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\ServiceGroup;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Models\Workspace;

class DashboardController extends Controller
{
    use UsesUserModel;

    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'count' => [
                'users' => self::getUserClass()::count(),
                'workspaces' => Workspace::count(),
            ],
            'is_configured_service' => ServiceManager::isConfigured(
                ServiceManager::services()->group(ServiceGroup::SOCIAL)->getNames()
            ),
        ]);
    }
}
