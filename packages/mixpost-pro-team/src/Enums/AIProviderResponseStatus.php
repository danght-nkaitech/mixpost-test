<?php

namespace Inovector\Mixpost\Enums;

enum AIProviderResponseStatus: string
{
    case OK = 'ok';

    case EXCEEDED_RATE_LIMIT = 'exceeded_rate_limit';

    case ERROR = 'error';

    case UNAUTHORIZED = 'unauthorized';
    case UNDEFINED = 'undefined';
}
