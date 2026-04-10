<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Builders\User\UserQuery;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\User\StoreUser;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\User\UpdateUser;
use Inovector\MixpostEnterprise\Http\Base\Resources\UserResource;

class UsersController extends Controller
{
    use UsesAuth;
    use UsesUserModel;

    public function index(Request $request): Response
    {
        $users = UserQuery::apply($request)->with('admin')->latest()->paginate(20)->onEachSide(1)->withQueryString();

        return Inertia::render('Panel/Users/Users', [
            'users' => fn () => UserResource::collection($users),
            'filter' => [
                'keyword' => $request->query('keyword', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Users/CreateEdit', [
            'mode' => 'create',
        ]);
    }

    public function store(StoreUser $storeUser): RedirectResponse
    {
        $user = $storeUser->handle();

        return redirect()->route('mixpost_e.users.view', ['user' => $user->id])->with('success', __('mixpost-enterprise::user.user_created'));
    }

    public function view(Request $request): Response
    {
        $user = self::getUserClass()::with('admin', 'workspaces')->findOrFail($request->route('user'));

        return Inertia::render('Panel/Users/View', [
            'user' => new UserResource($user),
            'emailVerification' => (new OnboardingConfig)->get('email_verification'),
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = self::getUserClass()::with('admin')->findOrFail($request->route('user'));

        return Inertia::render('Panel/Users/CreateEdit', [
            'mode' => 'edit',
            'user' => new UserResource($user),
        ]);
    }

    public function update(UpdateUser $updateUser): RedirectResponse
    {
        $updateUser->handle();

        return redirect()->back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $user = self::getUserClass()::findOrFail($request->route('user'));

        if ($user->id !== self::getAuthGuard()->id()) {
            self::getUserClass()::findOrFail($request->route('user'))->delete();

            return redirect()
                ->route('mixpost_e.users.index')
                ->with('success', "User {$user->name} deleted");
        }

        return redirect()->back()->with('error', __('mixpost-enterprise::dashboard.cant_delete'));
    }
}
