@props(['icon' => 'fa-regular fa-folder-open', 'title'])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <i class="{{ $icon }} d-block"></i>
    <div class="fw-semibold">{{ $title }}</div>
    @if (trim($slot) !== '')
        <div class="small mt-1">{{ $slot }}</div>
    @endif
</div>
