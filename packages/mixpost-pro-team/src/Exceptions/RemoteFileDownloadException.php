<?php

namespace Inovector\Mixpost\Exceptions;

use Exception;

class RemoteFileDownloadException extends Exception
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
