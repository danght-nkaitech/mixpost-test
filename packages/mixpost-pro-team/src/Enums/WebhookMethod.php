<?php

namespace Inovector\Mixpost\Enums;

enum WebhookMethod: string
{
    case POST = 'post';
    case GET = 'get';
    case PUT = 'put';
    case DELETE = 'delete';
}
