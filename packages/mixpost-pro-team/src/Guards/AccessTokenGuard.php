<?php

namespace Inovector\Mixpost\Guards;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\Request;
use Inovector\Mixpost\Models\UserToken;

class AccessTokenGuard
{
    protected Factory $auth;

    public function __construct(Factory $auth)
    {
        $this->auth = $auth;
    }

    public function __invoke(Request $request)
    {
        if ($token = $this->getTokenFromRequest($request)) {
            $accessToken = UserToken::findToken($token);

            if (! $this->isValidAccessToken($accessToken)) {
                return;
            }

            if (method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
                method_exists($accessToken->getConnection(), 'setRecordModificationState')) {
                tap($accessToken->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($accessToken) {
                    $accessToken->forceFill(['last_used_at' => now()])->save();

                    $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
                });
            } else {
                $accessToken->forceFill(['last_used_at' => now()])->save();
            }

            return $accessToken->user;
        }
    }

    protected function getTokenFromRequest(Request $request): ?string
    {
        $token = $request->bearerToken();

        return ! empty($token) ? $token : null;
    }

    protected function isValidAccessToken(?UserToken $accessToken): bool
    {
        if (! $accessToken) {
            return false;
        }

        return ! $accessToken->expires_at || ! $accessToken->expires_at->isPast();
    }
}
