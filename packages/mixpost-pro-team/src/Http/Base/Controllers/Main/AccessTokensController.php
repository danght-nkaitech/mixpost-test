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
use Illuminate\Support\Facades\Validator;
use Inovector\Mixpost\Support\Log;
use Inovector\Mixpost\Concerns\UsesUserModel;

class AccessTokensController extends Controller
{
    use UsesAuth, UsesUserModel;

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

    public function issueForExternalSystem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only(['signature', 'email']), [
            'signature' => ['required', 'string'],
            'email'     => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $tokenName = 'xroid';
        $secretKey = config('mixpost.token_issue_secret');
        // Tạo chuỗi ký dựa trên email và tokenName để đảm bảo mỗi token có một chữ ký duy nhất
        $rawSignatureString = $request->input('email') . ':' . $tokenName;
        // Tính toán chữ ký HMAC-SHA256
        $signature = hash_hmac('sha256', $rawSignatureString, $secretKey);

        // So sánh chữ ký đã tính với chữ ký gửi đến
        if (!hash_equals($signature, $request->input('signature'))) {
            Log::warning('External API: Invalid signature.', ['email' => $request->input('email')]);
            return response()->json(['message' => 'Unauthorized: Invalid signature.'], 401);
        }

        try {
            $user = self::getUserClass()::where('email', $request->input('email'))->first();
            if (! $user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $token = $user->createToken($tokenName, null);
            return response()->json(['token' => $token['plain_text_token']], 201);
        } catch (\Exception $e) {
            Log::error('External API: Error issuing token.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }
}
