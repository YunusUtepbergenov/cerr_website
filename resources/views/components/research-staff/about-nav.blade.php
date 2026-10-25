@props(['active' => 'staff', 'sectors' => null, 'currentSector' => null])

<aside class="rs-side">
    <nav class="rs-nav-card" aria-label="@lang('messages.about')">
        <h2 class="rs-nav-title">@lang('messages.about')</h2>
        <ul class="rs-nav-list">
            <li><a href="{{ route('about') }}" wire:navigate @class(['is-active' => $active === 'about'])>@lang('messages.objectives')</a></li>
            <li><a href="{{ route('history') }}" wire:navigate @class(['is-active' => $active === 'history'])>@lang('messages.history')</a></li>
            <li><a href="{{ route('leadership') }}" wire:navigate @class(['is-active' => $active === 'leadership'])>@lang('messages.leadership')</a></li>
            <li><a href="{{ route('structure') }}" wire:navigate @class(['is-active' => $active === 'structure'])>@lang('messages.structure')</a></li>
            <li><a href="{{ route('research-staff.index') }}" wire:navigate @class(['is-active' => $active === 'staff'])>@lang('staff.title')</a></li>
            <li><a href="{{ route('open-data.index') }}" wire:navigate @class(['is-active' => $active === 'open-data'])>@lang('messages.open_data')</a></li>
        </ul>
    </nav>

    @if ($sectors)
        <nav class="rs-nav-card rs-nav-card--sectors" aria-label="@lang('staff.other_sectors')">
            <h2 class="rs-nav-title">@lang('staff.sectors_title')</h2>
            <ul class="rs-nav-list rs-nav-list--sectors">
                @foreach ($sectors as $sector)
                    <li style="--rs-accent: {{ $sector['accent'] }}">
                        <a href="{{ route('research-staff.sector', $sector['slug']) }}" wire:navigate @class(['is-active' => $currentSector === $sector['slug']])>
                            <span class="rs-pill-dot"></span>{{ __('staff.sector_short_'.$sector['key']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    <a class="rs-cta" href="{{ route('contact') }}" wire:navigate>
        <span class="rs-cta-text">
            <strong>@lang('staff.write_us')</strong>
            <span>@lang('staff.write_us_text')</span>
        </span>
        <span class="rs-cta-btn">@lang('messages.contacts') <i class="fa-solid fa-arrow-right"></i></span>
    </a>
</aside>
