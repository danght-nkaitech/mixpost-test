<?php

namespace Inovector\Mixpost\Exceptions;

use Exception;

class ChunkedUploadSessionNotFound extends Exception
{
    public function __construct(string $uploadUuid)
    {
        parent::__construct("Chunked upload session not found: $uploadUuid", 404);
    }
}
