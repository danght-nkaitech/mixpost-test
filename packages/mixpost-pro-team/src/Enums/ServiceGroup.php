<?php

namespace Inovector\Mixpost\Enums;

enum ServiceGroup: string
{
    case MISCELLANEOUS = 'miscellaneous';
    case SOCIAL = 'social';
    case AI = 'ai';
    case MEDIA = 'media';
    case URL_SHORTENER = 'url_shortener';
}
