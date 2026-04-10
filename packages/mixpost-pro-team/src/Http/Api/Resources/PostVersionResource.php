<?php

namespace Inovector\Mixpost\Http\Api\Resources;

use Inovector\Mixpost\Http\Base\Resources\PostVersionResource as BasePostVersionResource;

class PostVersionResource extends BasePostVersionResource
{
    public function toArray($request)
    {
        return [
            'account_id' => $this->account_id,
            'is_original' => $this->is_original,
            'content' => $this->content(),
            'options' => $this->options(),
        ];
    }
}
