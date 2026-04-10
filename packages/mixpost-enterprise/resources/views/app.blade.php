@php
    use Inovector\Mixpost\Mixpost;
    use Inovector\MixpostEnterprise\MixpostEnterprise;
    use Inovector\Mixpost\Util;
    use Inovector\Mixpost\Facades\Theme;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Mixpost::getLocaleDirection() }}" class="scroll-smooth overflow-x-hidden">
<head>
    <title inertia>{{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="default_locale" content="{{ Util::config('default_locale') }}">
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('mixpost::partial.favicon')
    @include('mixpost-enterprise::partial.headScripts')
    {!! Theme::render() !!}
    {{ MixpostEnterprise::assets() }}
    @routes
    @inertiaHead
</head>
<body class="font-sans">
@include('mixpost-enterprise::partial.bodyScripts')
@inertia
</body>
</html>
