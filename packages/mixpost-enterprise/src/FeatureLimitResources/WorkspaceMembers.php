<?php

namespace Inovector\MixpostEnterprise\FeatureLimitResources;

use Inovector\MixpostEnterprise\Abstracts\FeatureLimitResource;
use Inovector\MixpostEnterprise\FeatureLimitFormFields\CountNumber;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

class WorkspaceMembers extends FeatureLimitResource
{
    public string $name = 'Workspace Members';

    public string $description = 'The maximum number of members in a workspace.';

    public function form(): array
    {
        return [
            CountNumber::make('count')->default(function () {
                return 5;
            }),
        ];
    }

    public function validator(?object $data = null): FeatureLimitResponse
    {
        $value = $this->getValue('count');

        if ($value === null) {
            return $this->makePasses();
        }

        $totalCount = $data->workspace->users()->count() + $data->workspace->invitations()->count();

        if ($totalCount < (int) $value) {
            return $this->makePasses();
        }

        return $this->makeFails()
            ->withMessages(__('mixpost-enterprise::feature_limit.members_limit_reached', ['limit' => $value]));
    }
}
