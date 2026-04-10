<?php

namespace Inovector\Mixpost\Concerns\Model;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\UserToken;

trait UserHasTokens
{
    public function tokens(): HasMany
    {
        return $this->hasMany(UserToken::class, 'user_id');
    }

    public function createToken(string $name, ?DateTimeInterface $expiresAt = null): array
    {
        $plainTextToken = $this->generateTokenString();

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
        ]);

        return [
            'model' => $token,
            'plain_text_token' => $plainTextToken,
        ];
    }

    public function generateTokenString(): string
    {
        return sprintf(
            '%s%s',
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );
    }
}
