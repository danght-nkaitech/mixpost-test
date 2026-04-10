<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Onboarding;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Support\TimezoneList;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Onboarding\Register;
use League\CommonMark\CommonMarkConverter;

class RegisterController extends Controller
{
    use UsesUserModel;

    public function create(Request $request): Response|RedirectResponse
    {
        if (! self::getUserClass()::exists()) {
            return redirect()->route('mixpost.installation');
        }

        $configs = (new OnboardingConfig($request))->all();
        $configs['terms_accept_description'] = (new CommonMarkConverter)->convert($configs['terms_accept_description'])->getContent();

        return Inertia::render('Onboarding/Register', [
            'configs' => $configs,
            'timezone_list' => (new TimezoneList)->splitGroup()->list(),
            'invitation' => [
                'email' => $request->query('email'),
            ],
        ]);
    }

    public function store(Register $request): RedirectResponse
    {
        $request->handle();

        if ($invitation = $request->invitation()) {
            return redirect()->route('mixpost_e.invitations.view', ['invitation' => $invitation->uuid]);
        }

        return redirect()->route('mixpost.home');
    }
}
