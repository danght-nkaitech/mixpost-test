<?php

namespace Inovector\Mixpost\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Util;

class PostActivityResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'reactions' => $this->getReactions(),
            'type' => $this->type?->name,
            'data' => $this->data,
            'text' => $this->text,
            'date_times' => $this->getFormatedDateTimeFromData(),
            'children_count' => $this->whenCounted('children'),
            'is_child' => (bool) $this->parent_id,
            'timestamps' => [
                'Iso8601' => [
                    'created_at' => $this->created_at->utc()->toIso8601String(),
                    'updated_at' => $this->updated_at->utc()->toIso8601String(),
                ],
                'localized' => [
                    'created_at' => Util::dateTimeFormat($this->created_at),
                    'updated_at' => Util::dateTimeFormat($this->updated_at),
                ],
            ],
        ];
    }

    protected function getReactions()
    {
        if (! $this->resource->relationLoaded('reactions')) {
            return [];
        }

        return $this->groupedReactions();
    }

    protected function getFormatedDateTimeFromData(): array
    {
        if (! $this->data) {
            return [];
        }

        $dates = [];

        foreach ($this->data as $key => $value) {
            if (! $value) {
                continue;
            }

            if (! Util::isTimestampString($value)) {
                continue;
            }

            $timestamp = Carbon::parse($value)->utc();

            $dates[$key] = [
                'Iso8601' => $timestamp->toIso8601String(),
                'localized' => Util::dateTimeFormat($timestamp),
            ];
        }

        return $dates;
    }
}
