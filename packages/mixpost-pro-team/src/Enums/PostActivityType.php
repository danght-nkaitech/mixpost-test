<?php

namespace Inovector\Mixpost\Enums;

enum PostActivityType: int
{
    case COMMENT = 0;

    case CREATED = 1;

    case SET_DRAFT = 2;

    case UPDATED_SCHEDULE_TIME = 3;

    case SCHEDULED = 4;
    case SCHEDULE_PROCESSING = 5;

    case PUBLISHED = 6;

    case PUBLISHED_FAILED = 7;

    case DELETED_FROM_SOCIAL_PLATFORMS = 8;
}
