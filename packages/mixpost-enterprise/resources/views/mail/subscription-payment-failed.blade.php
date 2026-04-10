@component('mixpost::mail.components.message')
# {{ __('mixpost-enterprise::error.payment_failed') }}

{{__('mixpost-enterprise::workspace.workspace')}}: {{ $workspace->name }}

{{__('mixpost-enterprise::finance.update_payment_information')}}

<x-mail::button :url="$url">
    {{__('mixpost-enterprise::finance.confirm_update_payment_information')}}
</x-mail::button>

<x-slot:subcopy>
@lang('mixpost::mail.trouble_clicking_btn',
[
'actionText' => __('mixpost-enterprise::finance.confirm_update_payment_information'),
]
) <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>
@endcomponent
