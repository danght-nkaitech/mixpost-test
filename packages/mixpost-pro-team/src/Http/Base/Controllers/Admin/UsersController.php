<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Builders\User\UserQuery;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Http\Base\Requests\Admin\StoreUser;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdateUser;
use Inovector\Mixpost\Http\Base\Resources\UserResource;
use Inovector\Mixpost\Mixpost;

class UsersController extends Controller
{
    use UsesUserModel;

    public function index(Request $request): Response|RedirectResponse
    {
        if (Mixpost::getEnterpriseConsoleUrl()) {
            return redirect()->to(Mixpost::getEnterpriseConsoleUrl().'/users');
        }

        $users = UserQuery::apply($request)
            ->with('admin')
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Admin/Users/Users', [
            'users' => fn () => UserResource::collection($users),
            'filter' => [
                'keyword' => $request->query('keyword', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/CreateEdit', [
            'mode' => 'create',
        ]);
    }

    public function store(StoreUser $storeUser): RedirectResponse
    {
        $storeUser->handle();

        return redirect()->route('mixpost.users.index')->with('success', __('mixpost::user.user_created'));
    }

    public function view(Request $request): Response
    {
        $user = self::getUserClass()::with('admin', 'workspaces')->findOrFail($request->route('user'));

        return Inertia::render('Admin/Users/View', [
            'user' => new UserResource($user),
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = self::getUserClass()::with('admin')->findOrFail($request->route('user'));

        return Inertia::render('Admin/Users/CreateEdit', [
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

        if ($user->id !== Auth::id()) {
            self::getUserClass()::findOrFail($request->route('user'))->delete();

            Settings::forgetAll($user->id);

            return redirect()
                ->route('mixpost.users.index')
                ->with('success', "User {$user->name} deleted");
        }

        return redirect()->back()->with('error', __('mixpost::error.self_deletion_not_allowed'));
    }
}
