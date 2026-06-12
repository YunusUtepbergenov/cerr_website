@props(['label', 'value', 'icon' => null, 'accent' => null])

<div {{ $attributes->merge(['class' => 'card stat-card'.($accent ? ' accent-'.$accent : '')]) }}>
    <div class="label">
        @if ($icon)<i class="{{ $icon }} me-1"></i>@endif
        {{ $label }}
    </div>
    <div class="value">{{ $value }}</div>
</div>
