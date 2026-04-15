<?php

namespace Inovector\Mixpost\Abstracts;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Inovector\Mixpost\Concerns\Model\TwoFactorAuthenticatable;
use Inovector\Mixpost\Concerns\Model\UserHasSettings;
use Inovector\Mixpost\Concerns\Model\UserHasTokens;
use Inovector\Mixpost\Concerns\Model\UserHasWorkspaces;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Models\Admin;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\Notifications\ResetPassword;

abstract class User extends Authenticatable implements HasLocalePreference
{
    use Notifiable;
    use TwoFactorAuthenticatable;
    use UserHasSettings;
    use UserHasTokens;
    use UserHasWorkspaces;

    protected $fillable = [
        'name',
        'email',
        'password',
        'firebase_uid',
        'firebase_linked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'firebase_linked_at' => 'datetime',
    ];

    public function preferredLocale(): string
    {
        return Settings::get('locale', $this->id);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify((new ResetPassword($token))->locale(App::getLocale()));
    }

    public function setActiveWorkspace(Workspace $workspace): void
    {
        $this->settings()->updateOrCreate(
            [
                'name' => 'active_workspace',
                'user_id' => $this->id,
            ],
            ['payload' => $workspace->id]
        );
    }

    public function getActiveWorkspace()
    {
        $workspaceId = $this->settings()
            ->where('name', 'active_workspace')
            ->value('payload');

        if (! $workspaceId) {
            return null;
        }

        return $this->workspaces()->where('workspace_id', $workspaceId)->first();
    }
}
