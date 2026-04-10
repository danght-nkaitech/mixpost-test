<?php

namespace Inovector\Mixpost\SocialProviders\Threads\Concerns;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Support\Log;

trait ManagesCallbacks
{
    /**
     * @throws Exception
     */
    public function handleUninstallRequest(Request $request): JsonResponse
    {
        $data = $this->parseSignature($request);

        $account = Account::withoutWorkspace()->where('provider_id', $data['user_id'])->first();

        if (! $account) {
            Log::error('Data Deletion Request Callback - Account not found', ['provider' => 'Threads', 'provider_id' => $data['user_id']]);
            throw new Exception('Account not found', 404);
        }

        WorkspaceManager::setCurrent($account->workspace);

        $account->setUnauthorized();

        return response()->json([
            'url' => route('mixpost.home'),
            'confirmation_code' => $account->uuid,
        ]);
    }

    private function parseSignature(Request $request): array
    {
        $signedRequest = $request->input('signed_request');

        [$encodedSig, $payload] = explode('.', $signedRequest, 2);

        // Decode the data
        $sig = $this->base64_url_decode($encodedSig);
        $data = json_decode($this->base64_url_decode($payload), true);

        // Confirm the signature
        $expectedSig = hash_hmac('sha256', $payload, $this->clientSecret, true);

        if ($sig !== $expectedSig) {
            throw new Exception('Bad Signed JSON signature!', 400);
        }

        return $data;
    }

    private function base64_url_decode(string $input): false|string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
