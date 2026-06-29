@props(['change' => null])
@php
    // null => brand new (no prior baseline), int => percentage change, 0 => flat.
    if ($change === null) {
        [$cls, $icon, $text] = ['trend-new', 'fa-star', __('admin.dashboard.trend_new')];
    } elseif ($change > 0) {
        [$cls, $icon, $text] = ['trend-up', 'fa-arrow-trend-up', '+'.$change.'%'];
    } elseif ($change < 0) {
        [$cls, $icon, $text] = ['trend-down', 'fa-arrow-trend-down', $change.'%'];
    } else {
        [$cls, $icon, $text] = ['trend-flat', 'fa-minus', '0%'];
    }
@endphp
<span {{ $attributes->merge(['class' => 'trend-badge '.$cls]) }}>
    <i class="fa-solid {{ $icon }}"></i> {{ $text }}
</span>
