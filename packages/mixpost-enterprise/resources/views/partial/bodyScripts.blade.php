@if (\Inovector\MixpostEnterprise\Util::isWorkspaceRoutes(request()) || !\Inovector\Mixpost\Util::isAdminConsole(request()))
    @if($scripts = \Inovector\MixpostEnterprise\Facades\ScriptsConfig::get('body'))
        {!! $scripts !!}
    @endif
@endif
