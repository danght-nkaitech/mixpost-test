<?php

namespace Inovector\Mixpost\Events\AI;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Inovector\Mixpost\Responses\AIProviderResponse;

class AITextGenerated
{
    use Dispatchable, SerializesModels;

    public AIProviderResponse $response;

    public function __construct(AIProviderResponse $response)
    {
        $this->response = $response;
    }
}
