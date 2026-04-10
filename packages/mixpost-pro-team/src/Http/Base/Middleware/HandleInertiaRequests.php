<?php

namespace Inovector\Mixpost\Http\Base\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Middleware;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Broadcast;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesFileConfig;
use Inovector\Mixpost\Concerns\UsesUserResource;
use Inovector\Mixpost\Enums\FileSizeUnit;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Facades\Theme;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Util;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    use UsesAuth;
    use UsesFileConfig;
    use UsesUserResource;

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'mixpost::layouts.app';

    /**
     * Determine the current asset version.
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        if (file_exists($manifest = public_path('vendor/mixpost/manifest.json'))) {
            return md5_file($manifest);
        }

        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => $this->auth(),
            'ziggy' => function () use ($request) {
                // TODO: inject most used enterprise routes here (example: create workspace, registration, workspace settings, workspace billing, etc.)
                // Mixpost::getEnterpriseRoutes()
                return array_merge((new Ziggy)->filter(['mixpost.*'])->toArray(), [
                    'location' => $request->url(),
                    'workspace_id' => $this->getWorkspaceId($request),
                ]);
            },
            'broadcast' => Broadcast::echoOptions(),
            'is_admin_console' => Util::isAdminConsole($request),
            'flash' => function () use ($request) {
                return [
                    'success' => $request->hasSession() ? $request->session()->get('success') : null,
                    'warning' => $request->hasSession() ? $request->session()->get('warning') : null,
                    'error' => $request->hasSession() ? $request->session()->get('error') : null,
                    'info' => $request->hasSession() ? $request->session()->get('info') : null,
                ];
            },
            'app' => [
                'name' => Config::get('app.name'),
                'horizon_path' => Config::get('horizon.path'),
            ],
            'mixpost' => [
                'core_path' => Util::corePath(),
                'docs_link' => 'https://docs.mixpost.app',
                'config' => [
                    'mime_types' => Util::config('mime_types'),
                    'chunked_upload' => [
                        'threshold' => $this->chunkedUploadThreshold(FileSizeUnit::BYTES),
                    ],
                ],
                'settings' => [
                    'locale' => Settings::get('locale'),
                    'timezone' => Settings::get('timezone'),
                    'time_format' => Settings::get('time_format'),
                    'week_starts_on' => Settings::get('week_starts_on'),
                ],
                'theme' => [
                    'logo' => Theme::config()->get('logo_url'),
                    'colors' => Theme::colors(),
                ],
                'features' => [
                    'api_access_tokens' => Features::isApiAccessTokenEnabled(),
                ],
                'enterpriseConsole' => [
                    'url' => Mixpost::getEnterpriseConsoleUrl(),
                    'registration_url' => Mixpost::getRegistrationUrl(),
                    'create_workspace_url' => Mixpost::getCreateWorkspaceUrl(),
                    'has_workspace_urls' => Mixpost::hasWorkspaceUrls(),
                    'workspace_settings_url' => Mixpost::getWorkspaceSettingsUrl(),
                    'workspace_billing_url' => Mixpost::getWorkspaceBillingUrl(),
                    'workspace_upgrade_url' => Mixpost::getWorkspaceUpgradeUrl(),
                    'stop_impersonating_url' => Mixpost::getStopImpersonatingUrl(),
                    'multiple_workspace_enabled' => Mixpost::getMultipleWorkspaceEnabled(),
                    'is_system_webhook_enabled' => Mixpost::isSystemWebhookEnabled(),
                ],
            ],
        ]);
    }

    protected function auth(): array
    {
        if (! self::getAuthGuard()->check()) {
            return [
                'user' => null,
                'workspaces' => [],
                'impersonating' => false,
            ];
        }

        $user = self::getAuthGuard()->user();

        // If `Auth Middleware` was not resolved first
        // return empty auth
        if (! $user instanceof User) {
            return [];
        }

        $userResourceClass = self::getUserResourceClass();

        return [
            'user' => new $userResourceClass($user->load(['admin', 'workspaces'])),
            'impersonating' => Mixpost::impersonating(),
        ];
    }

    protected function getWorkspaceId(Request $request)
    {
        // Exclude from Admin Console
        if (Util::isAdminConsole($request)) {
            return null;
        }

        return $request->route('workspace');
    }
}
