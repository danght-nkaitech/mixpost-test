<?php

namespace Inovector\Mixpost\Enums;

use Inovector\Mixpost\Concerns\Enum\EnumHandyMethods;

enum WorkspaceUserRole: string
{
    use EnumHandyMethods;

    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';
}
