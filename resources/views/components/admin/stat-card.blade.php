@props(['label', 'value', 'icon' => null, 'accent' => null])

<div {{ $attributes->merge(['class' => 'card stat-card'.($accent ? ' accent-'.$accent : '')]) }}>
    @if ($icon)
        <span class="stat-icon"><i class="{{ $icon }}"></i></span>
    @endif
    <div>
        <div class="label">{{ $label }}</div>
        <div class="value">{{ $value }}</div>
    </div>
</div>
