<?php

namespace Inovector\MixpostEnterprise\Http\Base\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Facades\Theme;
use Inovector\Mixpost\Features;
use Inovector\Mixpost\Mixpost;
use Inovector\MixpostEnterprise\Facades\Impersonation;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;
use Inovector\MixpostEnterprise\Util;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    use UsesAuth;

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'mixpost-enterprise::app';

    /**
     * Determine the current asset version.
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        if (file_exists($manifest = public_path('vendor/mixpost-enterprise/manifest.json'))) {
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
                return array_merge((new Ziggy)->filter(['mixpost.*', 'mixpost_e.*'])->toArray(), [
                    'location' => $request->url(),
                    'workspace_id' => $this->getWorkspaceId($request),
                ]);
            },
            'flash' => function () use ($request) {
                return [
                    'success' => $request->hasSession() ? $request->session()->get('success') : null,
                    'warning' => $request->hasSession() ? $request->session()->get('warning') : null,
                    'error' => $request->hasSession() ? $request->session()->get('error') : null,
                    'info' => $request->hasSession() ? $request->session()->get('info') : null,
                ];
            },
            'app' => [
                'name' => config('app.name'),
            ],
            'mixpost' => [
                'docs_link' => 'https://docs.mixpost.app',
                'settings' => [
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
                'configs' => [
                    'system' => [
                        'multiple_workspace_enabled' => Mixpost::getMultipleWorkspaceEnabled(),
                    ],
                ],
            ],
        ]);
    }

    protected function getWorkspaceId(Request $request): ?string
    {
        if (! Util::isWorkspaceRoutes($request)) {
            return null;
        }

        // Display only on workspace routes
        return $request->route('workspace');
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

        return [
            'user' => new UserResource($user->load(['admin', 'workspaces'])),
            'impersonating' => Impersonation::impersonating(),
        ];
    }
}
