<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Main;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inovector\Mixpost\Actions\TwoFactorAuth\ConfirmTwoFactorAuth;
use Inovector\Mixpost\Actions\TwoFactorAuth\DisableTwoFactorAuth;
use Inovector\Mixpost\Actions\TwoFactorAuth\EnableTwoFactorAuth;
use Inovector\Mixpost\Actions\TwoFactorAuth\RegenerateTwoFactorAuthRecoveryCodes;

class TwoFactorAuthController extends Controller
{
    public function enable(EnableTwoFactorAuth $enable): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasTwoFactorAuthEnabled()) {
            abort(403);
        }

        $enable($user);

        $user->load('twoFactorAuth');

        return response()->json([
            'svg' => $user->twoFactorQrCodeSvg(),
            'secret_key' => $user->twoFactorAuthSecretKey(),
        ]);
    }

    public function confirm(ConfirmTwoFactorAuth $confirm): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasTwoFactorAuthEnabled()) {
            abort(403);
        }

        $confirm($user, Request::input('code'));

        return response()->json([
            'recovery_codes' => $user->twoFactorRecoveryCodes(),
        ]);
    }

    public function showRecoveryCodes(): JsonResponse
    {
        return response()->json([
            'recovery_codes' => Auth::user()->twoFactorRecoveryCodes(),
        ]);
    }

    public function regenerateRecoveryCodes(RegenerateTwoFactorAuthRecoveryCodes $regenerate): JsonResponse
    {
        $regenerate(Auth::user());

        return response()->json([
            'recovery_codes' => Auth::user()->twoFactorRecoveryCodes(),
        ]);
    }

    public function disable(DisableTwoFactorAuth $disable): Response
    {
        $disable(Auth::user());

        return response()->noContent();
    }
}
