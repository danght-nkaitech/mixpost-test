<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\WorkspaceUserRole;

class Invitation extends Model
{
    use HasFactory;
    use HasUuid;
    use UsesUserModel;

    protected $table = 'mixpost_e_invitations';

    protected $fillable = [
        'uuid',
        'invited_by',
        'user_id',
        'email',
        'role',
        'can_approve',
    ];

    protected $casts = [
        'role' => WorkspaceUserRole::class,
        'can_approve' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'invited_by', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id', 'id');
    }

    public function userByEmail(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'email', 'email');
    }

    public static function findByEmail(string $email)
    {
        return static::where('email', $email)->first();
    }

    public function isForUser($user): bool
    {
        return $this->user_id === $user->id || $this->email === $user->email;
    }

    public function accept(): void
    {
        $user = $this->user ?? $this->userByEmail;

        $this->workspace->attachUser(
            id: $user->id,
            role: $this->role,
            canApprove: $this->can_approve,
        );
    }
}
