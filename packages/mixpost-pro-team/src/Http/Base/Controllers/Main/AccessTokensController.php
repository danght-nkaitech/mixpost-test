<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\Mixpost\Http\Base\Requests\Main\StoreUserToken;
use Inovector\Mixpost\Http\Base\Resources\TokenResource;

class AccessTokensController extends Controller
{
    use UsesAuth;

    public function index(): Response
    {
        return Inertia::render('Main/AccessTokens', [
            'tokens' => fn () => TokenResource::collection(self::getAuthGuard()->user()->tokens()->latest()->paginate(20)),
        ]);
    }

    public function store(StoreUserToken $request): JsonResponse
    {
        return response()->json($request->handle(), 201);
    }

    public function delete(Request $request): RedirectResponse
    {
        $token = self::getAuthGuard()
            ->user()
            ->tokens()
            ->findOrFail($request->route('token'));

        $token->delete();

        return redirect()
            ->back()
            ->with('success', 'Access token deleted successfully.');
    }
}
