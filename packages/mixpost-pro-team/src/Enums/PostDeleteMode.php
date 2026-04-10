<?php

namespace Inovector\Mixpost\Enums;

enum PostDeleteMode: string
{
    case APP_ONLY = 'app_only';

    case SOCIAL_ONLY = 'social_only';

    case APP_AND_SOCIAL = 'app_and_social';
}
