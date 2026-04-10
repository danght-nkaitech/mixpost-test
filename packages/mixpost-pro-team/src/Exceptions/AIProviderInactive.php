<?php

namespace Inovector\Mixpost\Exceptions;

use Exception;

class AIProviderInactive extends Exception
{
    public function __construct($serviceName)
    {
        parent::__construct("Service used by this provider is inactive. Please enable `$serviceName` service in the services page.");
    }
}
