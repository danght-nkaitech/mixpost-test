<?php

namespace Inovector\MixpostEnterprise\Facades;

use Inovector\Mixpost\Facades\ServiceManager;

class WorkspaceServiceManager extends ServiceManager
{
    protected static function getFacadeAccessor()
    {
        return 'MixpostEnterpriseWorkspaceServiceManager';
    }
}
