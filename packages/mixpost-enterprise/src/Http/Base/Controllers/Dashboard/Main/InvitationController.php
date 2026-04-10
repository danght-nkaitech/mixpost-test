<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Dashboard\Main;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Http\Base\Resources\InvitationResource;
use Inovector\MixpostEnterprise\Models\Invitation;

class InvitationController extends Controller
{
    use UsesAuth;

    public function __invoke(Request $request): RedirectResponse|Response
    {
        $invitation = Invitation::findByUuid($request->route('invitation'));

        if (! $invitation) {
            return redirect()->route('mixpost.home');
        }

        if (! $invitation->isForUser(self::getAuthGuard()->user())) {
            abort(403);
        }

        $invitation->load(['workspace', 'author']);

        return Inertia::render('Dashboard/Main/Invitation', [
            'invitation' => (new InvitationResource($invitation))->resolve(),
        ]);
    }
}
