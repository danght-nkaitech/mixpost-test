<?php

namespace Inovector\Mixpost\Enums;

enum SocialProviderContentType: string
{
    case SINGLE = 'single';
    case THREAD = 'thread';
    case COMMENTS = 'comments';
}
