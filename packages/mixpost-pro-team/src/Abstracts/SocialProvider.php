<?php

namespace Inovector\Mixpost\Abstracts;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\UsesSocialProviderResponse;
use Inovector\Mixpost\Contracts\AccountResource;
use Inovector\Mixpost\Contracts\SocialProvider as SocialProviderContract;
use Inovector\Mixpost\Contracts\SocialProviderPostOptions as SocialProviderPostOptionsContract;
use Inovector\Mixpost\Enums\SocialProviderContentType;
use Inovector\Mixpost\Enums\SocialProviderResponseStatus;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Support\SocialProviderPostOptions;
use Inovector\Mixpost\Support\SocialProviderResponse;

abstract class SocialProvider implements SocialProviderContract
{
    use UsesSocialProviderResponse;

    /**
     * Some providers support one account (e.g., X, TikTok).
     * Others, like Facebook, need a user account to manage entities.
     */
    public bool $onlyUserAccount = true;

    public array $callbackResponseKeys = [];

    protected array $accessToken = [];

    protected Request $request;

    protected string $clientId = '';

    protected string $clientSecret = '';

    protected string $redirectUrl;

    protected array $values = [];

    const ACCESS_TOKEN_SESSION_NAME = 'mixpost_provider_access_token'; // TODO: remove this line?

    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $values = [])
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->values = $values;
    }

    public function identifier(): string
    {
        $className = basename(str_replace('\\', '/', get_class($this)));

        return Str::of($className)->replace('Provider', '')->snake();
    }

    public static function name(): string
    {
        $className = basename(str_replace('\\', '/', static::class));

        return Str::of($className)->replace('Provider', '');
    }

    public function isOnlyUserAccount(): bool
    {
        return $this->onlyUserAccount;
    }

    public function getCallbackResponse(): array
    {
        return $this->request->only($this->callbackResponseKeys);
    }

    public function setAccessToken(array $token = []): void
    {
        $this->accessToken = $token;

        $this->request->session()->put(self::ACCESS_TOKEN_SESSION_NAME, $token); // TODO: remove this line?
    }

    public function getAccessToken()
    {
        if (! empty($this->accessToken)) {
            return $this->accessToken;
        }

        $token = $this->request->session()->get(self::ACCESS_TOKEN_SESSION_NAME); // TODO: remove this line?

        if (! $token) {
            throw new Exception('Missing Access Token.');
        }

        return $token;
    }

    // Use this method outside of web app. For example, in Jobs..etc.
    public function useAccessToken(array $token = []): static
    {
        $this->accessToken = $token;

        return $this;
    }

    public function forgetAccessToken(): void
    {
        $this->accessToken = [];
        $this->request->session()->forget(self::ACCESS_TOKEN_SESSION_NAME); // TODO: remove this line?
    }

    public function hasRefreshToken(): bool
    {
        $refreshToken = Arr::get($this->getAccessToken(), 'refresh_token');

        return ! empty($refreshToken);
    }

    public function tokenIsAboutToExpire(): bool
    {
        $expires_in = $this->getAccessToken()['expires_in'];

        $expiresAt = Carbon::createFromTimestamp($expires_in, 'UTC');
        $minutesAhead = Carbon::now('UTC')->addMinutes(12);

        return $expiresAt->lte($minutesAhead);
    }

    public function updateToken(array $newAccessToken): void
    {
        $accessToken = array_merge($this->getAccessToken(), $newAccessToken);

        $this->useAccessToken($accessToken);

        if ($account = Account::find($this->values['account_id'])) {
            $account->updateAccessToken($accessToken);
        }
    }

    public function publishComment(string $text, string $postId, array $params = []): SocialProviderResponse
    {
        return $this->response(SocialProviderResponseStatus::UNAUTHORIZED, []);
    }

    public function getHttpClient(): Http
    {
        return resolve(Http::class);
    }

    public function buildUrlFromBase(string $url, array $params): string
    {
        return $url.'?'.http_build_query($params, '', '&');
    }

    public function rateLimitExceedContext(int $retryAfter, ?string $customText = null): array
    {
        $defaultText = 'The rate limit has been exceeded';
        $date = Carbon::now('UTC')->addSeconds($retryAfter)->format('Y-m-d H:i');

        return [
            'rate_limit_exceed' => true,
            'message' => $customText ?? $defaultText,
            'next_attempt_at' => $date,
        ];
    }

    public static function postOptions(): SocialProviderPostOptionsContract
    {
        return new SocialProviderPostOptions;
    }

    public static function externalPostUrl(AccountResource $accountResource): string
    {
        return '';
    }

    public static function contentType(): SocialProviderContentType
    {
        return SocialProviderContentType::SINGLE;
    }

    public static function supportAnalytics(): bool
    {
        return true;
    }

    public static function mapErrorMessage(string $key): string
    {
        return match ($key) {
            'access_token_expired' => __('mixpost::account.access_token_expired'),
            'request_timeout' => __('mixpost::error.request_timeout'),
            'unknown_error' => __('mixpost::error.unknown_error'),
            default => $key
        };
    }
}
