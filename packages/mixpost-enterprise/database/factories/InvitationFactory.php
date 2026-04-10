<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Models\User;
use Inovector\MixpostEnterprise\Models\Invitation;
use Inovector\MixpostEnterprise\Models\Workspace;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory(),
            'uuid' => $this->faker->uuid(),
            'invited_by' => User::factory(),
            'user_id' => User::factory(),
            'email' => $this->faker->email(),
            'role' => WorkspaceUserRole::MEMBER,
            'can_approve' => false,
        ];
    }
}
