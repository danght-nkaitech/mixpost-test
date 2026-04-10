<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\PostingSchedule;
use Inovector\Mixpost\Models\Workspace;

class PostingScheduleFactory extends Factory
{
    protected $model = PostingSchedule::class;

    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory(),
            'times' => [],
        ];
    }
}
