@component('mixpost::mail.components.message')
{{ __('mixpost-enterprise::team.invited_join_workspace', ['name' => $workspace->name])}}

<x-mail::button :url="$url">
    {{__('mixpost-enterprise::workspace.accept_invitation')}}
</x-mail::button>

<x-slot:subcopy>
@lang('mixpost::mail.trouble_clicking_btn',
[
'actionText' => __('mixpost-enterprise::workspace.accept_invitation'),
]
) <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>
@endcomponent
