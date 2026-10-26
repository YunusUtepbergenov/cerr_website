@props(['icon'])

@php($map = str_starts_with($icon, 'map:') ? \App\Support\RegionShapes::path(substr($icon, 4)) : null)

@if ($map)
    <svg class="rs-icon rs-icon--map" viewBox="0 0 100 100" data-icon="{{ $icon }}" aria-hidden="true" focusable="false"><path d="{{ $map }}"/></svg>
@else
    <svg class="rs-icon" viewBox="0 0 24 24" data-icon="{{ $icon }}" aria-hidden="true" focusable="false">
        @switch($icon)
            @case('trend')
                <path d="M3 20h18"/><path d="M4.5 16l4.5-5 4 3 6.5-7.5"/><path d="M15 6.5h4.5V11"/>
                @break
            @case('bank')
                <path d="M3.5 10 12 5l8.5 5H3.5z"/><path d="M5.5 10v7M9.8 10v7M14.2 10v7M18.5 10v7"/><path d="M3 20h18"/>
                @break
            @case('factory')
                <path d="M3.5 20V9.5l5 3v-3l5 3v-3l5 3V20H3.5z"/><path d="M16.5 12.5V4.5h3v8"/><path d="M3 20h18"/>
                @break
            @case('globe')
                <circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17"/><path d="M12 3.5c3 3 3 14 0 17M12 3.5c-3 3-3 14 0 17"/>
                @break
            @case('cluster')
                <path d="M15.1 9.0L12.0 10.8L8.9 9.0L8.9 5.4L12.0 3.6L15.1 5.4ZM10.5 17.0L7.4 18.8L4.3 17.0L4.3 13.4L7.4 11.6L10.5 13.4ZM19.7 17.0L16.6 18.8L13.5 17.0L13.5 13.4L16.6 11.6L19.7 13.4Z"/>
                @break
            @case('megaphone')
                <path d="M4 10.5v3a1 1 0 0 0 1 1h2.2l7.8 4V5.5l-7.8 4H5a1 1 0 0 0-1 1z"/><path d="M18 9.5a3.2 3.2 0 0 1 0 5"/><path d="M7.5 14.5l1.3 5"/>
                @break
            @case('database')
                <ellipse cx="12" cy="6" rx="7.5" ry="3"/><path d="M4.5 6v12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3V6"/><path d="M4.5 12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3"/>
                @break
            @case('rocket')
                <path d="M12 3c3 2.5 4.5 6.5 4 11l-4 3-4-3c-.5-4.5 1-8.5 4-11z"/><circle cx="12" cy="10" r="1.6"/><path d="M8 14l-2.5 1.5L7 19M16 14l2.5 1.5L17 19"/><path d="M10.5 17.5 12 21l1.5-3.5"/>
                @break
            @default
                <circle cx="12" cy="12" r="8"/>
        @endswitch
    </svg>
@endif
