<?php

namespace Inovector\MixpostEnterprise\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIALING = 'trialing';
    case PAST_DUE = 'past_due';
    case PAUSED = 'paused';
    case CANCELED = 'canceled';
    case INCOMPLETE = 'incomplete';
}
