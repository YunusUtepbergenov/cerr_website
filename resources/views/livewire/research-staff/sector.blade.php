@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
    @php($people = collect([$sector['head']])->filter()->concat($sector['staff']))
    @php($position = (int) $sectors->search(fn (array $item): bool => $item['key'] === $sector['key']))

    <section class="rs-page rs-sector-page" style="--rs-accent: {{ $sector['accent'] }}">
        <div class="rs-wrap">
            <header class="rs-hero rs-hero--sector">
                <nav class="article-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('home') }}">@lang('messages.main')</a>
                    <span class="sep">/</span>
                    <a href="{{ route('research-staff.index') }}" wire:navigate>@lang('staff.title')</a>
                    <span class="sep">/</span>
                    <span class="current">{{ __('staff.sector_short_'.$sector['key']) }}</span>
                </nav>
                <div class="rs-hero-body">
                    <div class="rs-hero-text rs-hero-text--icon">
                        <span class="rs-hero-icon" aria-hidden="true"><i class="fa-solid {{ $sector['icon'] }}"></i></span>
                        <p class="rs-eyebrow">
                            <span class="rs-pill-dot"></span>@lang('staff.sector')
                            <span class="rs-hero-num">{{ str_pad((string) ($position + 1), 2, '0', STR_PAD_LEFT) }} / {{ $sectors->count() }}</span>
                        </p>
                        <h1 class="rs-title rs-title--sector">{{ $name }}</h1>
                        <p class="rs-lead">{{ __('staff.sector_desc_'.$sector['key']) }}</p>
                        @if ($sector['head'])
                            @php($headName = Proto::text($sector['head']['name']))
                            <a class="rs-hero-head" href="{{ route('research-staff.show', $sector['head']['slug']) }}" wire:navigate>
                                <span class="rs-hero-head-photo">
                                    @if ($sector['head']['photo'])
                                        <img src="{{ asset($sector['head']['photo']) }}" alt="">
                                    @else
                                        <span class="rs-avatar rs-avatar--sm" aria-hidden="true">{{ Proto::initials($headName) }}</span>
                                    @endif
                                </span>
                                <span class="rs-hero-head-text">
                                    <span class="rs-hero-head-label">@lang('staff.head_of_sector')</span>
                                    <span class="rs-hero-head-name">{{ $headName }}</span>
                                </span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                    <x-research-staff.sector-ring class="rs-hero-ring" :sectors="$sectors" :current="$sector['key']" :value="$members" :label="trans_choice('staff.members', $members, ['count' => ''])" />
                </div>
                <p class="rs-note"><i class="fa-solid fa-flask"></i> @lang('staff.prototype_note')</p>
            </header>

            <div class="rs-layout">
                <div class="rs-main">
                    <h2 class="rs-section-title">@lang('staff.staff_of_sector')</h2>

                    <ul class="rs-people">
                        @foreach ($people as $person)
                            @php($personName = Proto::text($person['name']))
                            <li class="rs-person" data-person="{{ $person['slug'] }}">
                                <a class="rs-person-photo" href="{{ route('research-staff.show', $person['slug']) }}" wire:navigate tabindex="-1" aria-hidden="true">
                                    @if ($person['photo'])
                                        <img src="{{ asset($person['photo']) }}" alt="">
                                    @else
                                        <span class="rs-avatar">{{ Proto::initials($personName) }}</span>
                                    @endif
                                </a>
                                <div class="rs-person-body">
                                    @if ($person['is_head'])
                                        <span class="rs-role-tag">@lang('staff.head_of_sector')</span>
                                    @endif
                                    <h3 class="rs-person-name"><a href="{{ route('research-staff.show', $person['slug']) }}" wire:navigate>{{ $personName }}</a></h3>
                                    <span class="rs-person-rule"></span>
                                    <p class="rs-person-pos">
                                        {{ __('staff.position_'.$person['position']) }}
                                        @if ($person['degree'])
                                            <span class="rs-degree">{{ __('staff.degree_'.$person['degree']) }}</span>
                                        @endif
                                    </p>
                                    @if ($areas = Proto::items($person['research_areas']))
                                        <p class="rs-person-areas">{{ implode(' · ', $areas) }}</p>
                                    @endif
                                    <div class="rs-person-foot">
                                        <a class="rs-chip-link" href="mailto:{{ $person['email'] }}"><i class="fa-regular fa-envelope"></i>{{ $person['email'] }}</a>
                                        <a class="rs-btn" href="{{ route('research-staff.show', $person['slug']) }}" wire:navigate>@lang('staff.see_more') <i class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-research-staff.about-nav active="staff" :sectors="$sectors" :current-sector="$sector['slug']" />
            </div>
        </div>
    </section>
</div>
