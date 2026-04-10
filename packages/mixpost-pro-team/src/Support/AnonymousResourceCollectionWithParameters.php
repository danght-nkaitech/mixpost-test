<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inovector\Mixpost\Concerns\ResourceHasParameters;

class AnonymousResourceCollectionWithParameters extends AnonymousResourceCollection
{
    use ResourceHasParameters;

    public function toArray($request)
    {
        return $this->collection->map(function ($resource) use ($request) {
            return $resource->additionalFields($this->additionalFields)
                ->only($this->only)
                ->except($this->except)
                ->toArray($request);
        })->all();
    }
}
