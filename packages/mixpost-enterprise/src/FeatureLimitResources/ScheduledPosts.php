<?php

namespace Inovector\MixpostEnterprise\FeatureLimitResources;

use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Models\Post;
use Inovector\MixpostEnterprise\Abstracts\FeatureLimitResource;
use Inovector\MixpostEnterprise\FeatureLimitFormFields\CountNumber;
use Inovector\MixpostEnterprise\Support\FeatureLimitResponse;

class ScheduledPosts extends FeatureLimitResource
{
    public string $name = 'Scheduled posts';

    public string $description = 'The number of scheduled posts users can have at once.';

    public function form(): array
    {
        return [
            CountNumber::make('count')->default(function () {
                return 30;
            }),
        ];
    }

    public function validator(?object $data = null): FeatureLimitResponse
    {
        $value = $this->getValue('count');

        if ($value === null) {
            return $this->makePasses();
        }

        $count = Post::byWorkspace($data->workspace)
            ->where('status', PostStatus::SCHEDULED->value)
            ->count();

        if ($count < (int) $value) {
            return $this->makePasses();
        }

        return $this->makeFails()
            ->withMessages(__('mixpost-enterprise::feature_limit.max_scheduled_posts', ['value' => $value]));
    }
}
