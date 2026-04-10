<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inovector\Mixpost\Concerns\UsesUserModel;

class UserToken extends Model
{
    use HasFactory;
    use UsesUserModel;

    public $table = 'mixpost_user_tokens';

    protected $fillable = [
        'name',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'date',
    ];

    protected $hidden = [
        'token',
    ];

    public static function findToken($token)
    {
        return static::where('token', hash('sha256', $token))->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id');
    }
}
