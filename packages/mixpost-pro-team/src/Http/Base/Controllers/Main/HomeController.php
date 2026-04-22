<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Concerns\UsesWorkspaceModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;

class HomeController extends Controller
{
    use UsesUserModel;
    use UsesWorkspaceModel;

    protected array $workspaceColors = [
        'e74c3c', '3498db', '2ecc71', 'f39c12', '9b59b6',
        '1abc9c', 'e67e22', '34495e', 'e91e63', '00bcd4',
    ];

    public function __invoke(): RedirectResponse
    {
        $workspace = Auth::user()->getActiveWorkspace();

        if (! $workspace) {
            $workspace = Auth::user()->workspaces()->recentlyUpdated()->first();

            // If there is a recently updated workspace, set it as user active workspace
            if ($workspace) {
                Auth::user()->setActiveWorkspace($workspace);
            }
        }

        if (! $workspace) {
            if (Auth::user()->isAdmin()) {
                return redirect()->to(route('mixpost.workspaces.create'));
            } else {
                $workspace = $this->createDefaultWorkspace();
            }

        }

        return redirect()->to(route('mixpost.dashboard', ['workspace' => $workspace->uuid]));
    }

    protected function createDefaultWorkspace()
    {
        return DB::transaction(function () {
            $attributes = [
                'name'      => Auth::user()->name . ' ' . Carbon::now()->format('d/m/Y H:i'),
                'hex_color' => $this->workspaceColors[array_rand($this->workspaceColors)],
            ];

            // Set unlimited access for Enterprise workspaces so no payment platform is required
            if (\defined('Inovector\\MixpostEnterprise\\Enums\\WorkspaceAccessStatus::UNLIMITED')) {
                $attributes['access_status'] = \Inovector\MixpostEnterprise\Enums\WorkspaceAccessStatus::UNLIMITED;
            }

            $workspaceClass = self::getWorkspaceModelClass();
            $workspace = $workspaceClass::create($attributes);

            $workspace->attachUser(
                id: Auth::id(),
                role: WorkspaceUserRole::ADMIN,
                canApprove: true
            );

            if (method_exists($workspace, 'saveOwner')) {
                $workspace->saveOwner(Auth::user());
            }

            // Assign a free subscription plan if available (Enterprise)
            if (class_exists('Inovector\\MixpostEnterprise\\Actions\\Workspace\\OnboardWorkspace')) {
                (new \Inovector\MixpostEnterprise\Actions\Workspace\OnboardWorkspace)($workspace);
            }

            return $workspace;
        });
    }
}
