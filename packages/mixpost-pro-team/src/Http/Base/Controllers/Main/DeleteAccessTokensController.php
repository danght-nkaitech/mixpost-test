<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesAuth;

class DeleteAccessTokensController extends Controller
{
    use UsesAuth;

    public function __invoke(Request $request): RedirectResponse
    {
        self::getAuthGuard()
            ->user()
            ->tokens()
            ->whereIn('id', $request->input('tokens', []))
            ->delete();

        return redirect()
            ->back()
            ->with('success', 'Selected access tokens deleted successfully.');
    }
}
