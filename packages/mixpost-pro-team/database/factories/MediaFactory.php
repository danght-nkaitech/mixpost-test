<?php

namespace Inovector\Mixpost\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Models\Workspace;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        $size = $this->faker->randomDigit();

        return [
            'uuid' => $this->faker->uuid,
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->domainName,
            'mime_type' => $this->faker->mimeType(),
            'disk' => 'public',
            'path' => '',
            'size' => $size,
            'size_total' => $size,
            'conversions' => [],
        ];
    }
}
