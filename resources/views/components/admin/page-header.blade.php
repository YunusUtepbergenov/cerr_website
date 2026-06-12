@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <div class="subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="d-flex align-items-center gap-2 flex-wrap">{{ $slot }}</div>
    @endif
</div>
