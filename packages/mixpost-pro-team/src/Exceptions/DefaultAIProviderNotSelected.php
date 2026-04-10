<?php

namespace Inovector\Mixpost\Exceptions;

use Exception;

class DefaultAIProviderNotSelected extends Exception
{
    public function __construct()
    {
        parent::__construct('No default AI provider selected. Configure an AI service, then select it as default from AI settings page.');
    }
}
