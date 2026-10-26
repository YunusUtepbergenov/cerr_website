@use('App\Support\ResearchStaffPrototype', 'Proto')
@props(['sectors', 'current' => null, 'value', 'label'])

@php
    $size = 220;
    $radius = 92;
    $centre = $size / 2;
    $gapDegrees = 3.2;
    $counts = $sectors->map(fn (array $sector): int => $sector['staff']->count() + ($sector['head'] ? 1 : 0));
    $total = max(1, $counts->sum());
    $available = 360 - $gapDegrees * $sectors->count();
    $angle = -90;
    $point = fn (float $degrees): array => [
        $centre + $radius * cos(deg2rad($degrees)),
        $centre + $radius * sin(deg2rad($degrees)),
    ];
    $arcs = [];
    foreach ($sectors->values() as $index => $sector) {
        $sweep = $available * $counts[$index] / $total;
        [$x0, $y0] = $point($angle);
        [$x1, $y1] = $point($angle + $sweep);
        $arcs[] = [
            'sector' => $sector,
            'members' => $counts[$index],
            'd' => sprintf('M %.2f %.2f A %d %d 0 %d 1 %.2f %.2f', $x0, $y0, $radius, $radius, $sweep > 180 ? 1 : 0, $x1, $y1),
        ];
        $angle += $sweep + $gapDegrees;
    }
@endphp

<div {{ $attributes->class(['rs-ring', 'rs-ring--focused' => $current !== null]) }} x-data="{ hover: null }" style="--rs-ring-size: {{ $size }}px">
    <svg class="rs-ring-svg" viewBox="0 0 {{ $size }} {{ $size }}" role="img" aria-label="{{ $label }}">
        <circle class="rs-ring-track" cx="{{ $centre }}" cy="{{ $centre }}" r="{{ $radius }}"></circle>
        @foreach ($arcs as $index => $arc)
            <a href="{{ route('research-staff.sector', $arc['sector']['slug']) }}"
               class="rs-ring-arc{{ $current === $arc['sector']['key'] ? ' is-current' : '' }}"
               data-ring-sector="{{ $arc['sector']['key'] }}"
               style="--rs-arc: {{ $arc['sector']['accent'] }}; --rs-arc-index: {{ $index }}"
               @mouseenter="hover = '{{ $arc['sector']['key'] }}'" @mouseleave="hover = null"
               @focus="hover = '{{ $arc['sector']['key'] }}'" @blur="hover = null">
                <title>{{ Proto::sectorName($arc['sector']['key']) }} — {{ trans_choice('staff.members', $arc['members'], ['count' => $arc['members']]) }}</title>
                <path d="{{ $arc['d'] }}" pathLength="1"></path>
            </a>
        @endforeach
    </svg>

    <div class="rs-ring-centre" aria-hidden="true">
        <div class="rs-ring-default" x-show="hover === null">
            <strong>{{ $value }}</strong>
            <span>{{ $label }}</span>
        </div>
        @foreach ($arcs as $arc)
            <div class="rs-ring-hover" x-show="hover === '{{ $arc['sector']['key'] }}'" x-cloak style="--rs-arc: {{ $arc['sector']['accent'] }}">
                <em>{{ __('staff.sector_short_'.$arc['sector']['key']) }}</em>
                <strong>{{ $arc['members'] }}</strong>
                <span>{{ trans_choice('staff.members', $arc['members'], ['count' => '']) }}</span>
            </div>
        @endforeach
    </div>
</div>
