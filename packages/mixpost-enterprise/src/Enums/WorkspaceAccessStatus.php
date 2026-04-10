<?php

namespace Inovector\MixpostEnterprise\Enums;

enum WorkspaceAccessStatus: string
{
    case SUBSCRIPTION = 'subscription';
    case UNLIMITED = 'unlimited';
    case LOCKED = 'locked';
}
