<?php

namespace Inovector\Mixpost\Exceptions;

use Exception;

class CurrentWorkspaceCouldNotBeDetermined extends Exception
{
    public static function noWorkspaceFound(): self
    {
        return new static('The workspace manager could not find a workspace.');
    }
}
