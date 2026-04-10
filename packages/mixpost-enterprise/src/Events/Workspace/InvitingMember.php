<?php

namespace Inovector\MixpostEnterprise\Events\Workspace;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\MixpostEnterprise\Models\Workspace;

class InvitingMember
{
    use Dispatchable, SerializesModels;

    public Workspace $workspace;

    public string $email;

    public string $role;

    public function __construct(Workspace $workspace, string $email, string $role)
    {
        $this->workspace = $workspace;
        $this->email = $email;
        $this->role = $role;
    }
}
