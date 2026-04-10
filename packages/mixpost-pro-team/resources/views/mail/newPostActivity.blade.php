@php
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Enums\PostActivityType;
use Inovector\Mixpost\Facades\Settings;
use Inovector\Mixpost\Util;
@endphp
@component('mixpost::mail.components.message')
{{-- Message --}}
# {{ __("mixpost::post_activity.$type") }}

@if($activity->isComment())
{{ __('mixpost::post_activity.by', ['name' => $author]) }}
@endif

{{-- Panel --}}
<x-mail::panel>
@if($activity->isComment())
{!! nl2br($activity->text) !!}
@endif

@if($activity->type == PostActivityType::SCHEDULED)
    @php
        $datetime = Util::dateTimeFormat(
            Carbon::parse(Arr::get($activity->data, 'scheduled_at'))->utc(),
            Settings::get('timezone', $notifiable->id)
        );
    @endphp

    @if(Arr::get($activity->data, 'status') === strtolower(PostStatus::NEEDS_APPROVAL->name))
        @lang('mixpost::post_activity.schedule_approval', ['user' => $activity->user->name, 'datetime' => $datetime])
    @elseif(Arr::get($activity->data, 'status') === strtolower(PostStatus::SCHEDULED->name) && Arr::get($activity->data, 'with_approval', false))
        @lang('mixpost::post_activity.post_approved', ['user' => $activity->user->name, 'datetime' => $datetime])
    @else
        @lang('mixpost::post_activity.scheduled_post', ['user' => $activity->user->name, 'datetime' => $datetime])
    @endif
@endif

@if($activity->type == PostActivityType::UPDATED_SCHEDULE_TIME)
    @php
        $oldDatetime = Util::dateTimeFormat(
            Carbon::parse(Arr::get($activity->data, 'old_scheduled_at'))->utc(),
            Settings::get('timezone', $notifiable->id)
        );

        $newDatetime = Util::dateTimeFormat(
            Carbon::parse(Arr::get($activity->data, 'new_scheduled_at'))->utc(),
            Settings::get('timezone', $notifiable->id)
        );
    @endphp

    @if(Arr::get($activity->data, 'old_scheduled_at') && Arr::get($activity->data, 'new_scheduled_at'))
        @lang('mixpost::post_activity.update_scheduled_at', ['user' => $activity->user->name, 'old_datetime' => $oldDatetime, 'new_datetime' => $newDatetime])
    @endif
@endif

@if($activity->type == PostActivityType::SET_DRAFT)
    @if($activity->user->name)
        @lang('mixpost::post_activity.user_set_drafts', ['user' => $activity->user->name])
    @else
        @lang('mixpost::post_activity.set_drafts')
    @endif
@endif

@if($activity->type == PostActivityType::SCHEDULE_PROCESSING)
    @lang('mixpost::post_activity.publishing')
@endif

@if($activity->type == PostActivityType::PUBLISHED)
    @lang('mixpost::post_activity.post_published')
@endif

@if($activity->type == PostActivityType::PUBLISHED_FAILED)
    @lang('mixpost::post_activity.post_publishing_failed')
@endif
</x-mail::panel>

{{-- Action Button --}}
<x-mail::button :url="$actionUrl" color="primary">
{{ $actionText }}
</x-mail::button>

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
    @lang('mixpost::mail.trouble_clicking_btn',
    [
    'actionText' => $actionText,
    ]
    ) <span class="break-all">[{{ $actionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
@endcomponent
