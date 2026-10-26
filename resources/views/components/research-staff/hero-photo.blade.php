@props(['value', 'label'])

{{-- The Centre's building: fades into the hero banner, tinted with the sector accent. --}}
<div {{ $attributes->class(['rs-hero-photo']) }}>
    <img src="{{ asset('images/research-staff/cerr-building.webp') }}" srcset="{{ asset('images/research-staff/cerr-building-sm.webp') }} 640w, {{ asset('images/research-staff/cerr-building.webp') }} 1100w" sizes="(max-width: 860px) 100vw, 520px" width="1100" height="696" alt="" fetchpriority="high" decoding="async">
    <p class="rs-hero-stat"><strong>{{ $value }}</strong><span>{{ $label }}</span></p>
</div>
