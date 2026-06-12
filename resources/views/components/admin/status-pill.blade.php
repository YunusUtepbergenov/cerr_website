@props(['status'])

@php
    $labelKey = 'admin.news.status_short_'.$status;
    $label = \Illuminate\Support\Facades\Lang::has($labelKey) ? __($labelKey) : $status;
@endphp

<span {{ $attributes->merge(['class' => 'pill status-'.$status]) }}>{{ $label }}</span>
