@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    <section class="rs-page">
        <div class="rs-wrap">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">@lang('staff.title')</span>
            </nav>

            <header class="rs-hero">
                <div class="rs-hero-text">
                    <p class="rs-eyebrow">@lang('staff.eyebrow')</p>
                    <h1 class="rs-title">@lang('staff.title')</h1>
                    <p class="rs-lead">@lang('staff.lead')</p>
                </div>
                <ul class="rs-stats" aria-label="@lang('staff.title')">
                    <li><strong>{{ $sectors->count() }}</strong><span>{{ trans_choice('staff.sectors', $sectors->count(), ['count' => '']) }}</span></li>
                    <li><strong>{{ $totalPeople }}</strong><span>{{ trans_choice('staff.researchers', $totalPeople, ['count' => '']) }}</span></li>
                    <li><strong>{{ $withDegree }}</strong><span>{{ trans_choice('staff.with_degree', $withDegree, ['count' => '']) }}</span></li>
                </ul>
            </header>

            <p class="rs-note"><i class="fa-solid fa-flask"></i> @lang('staff.prototype_note')</p>

            <ul class="rs-sector-grid">
                @foreach ($sectors as $index => $sector)
                    @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
                    @php($head = $sector['head'])
                    @php($preview = $sector['staff']->take(4))
                    <li style="--rs-accent: {{ $sector['accent'] }}">
                        <a class="rs-sector-card" href="{{ route('research-staff.sector', $sector['slug']) }}" wire:navigate data-sector-card="{{ $sector['slug'] }}">
                            <span class="rs-sector-card-top">
                                <span class="rs-sector-icon" aria-hidden="true"><i class="fa-solid {{ $sector['icon'] }}"></i></span>
                                <span class="rs-sector-kicker">
                                    <span class="rs-sector-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="rs-sector-short">{{ __('staff.sector_short_'.$sector['key']) }}</span>
                                </span>
                            </span>
                            <h2 class="rs-sector-card-title">{{ Proto::sectorName($sector['key']) }}</h2>
                            <p class="rs-sector-card-desc">{{ __('staff.sector_desc_'.$sector['key']) }}</p>

                            @if ($head)
                                @php($headName = Proto::text($head['name']))
                                <span class="rs-sector-head">
                                    <span class="rs-sector-head-photo">
                                        @if ($head['photo'])
                                            <img src="{{ asset($head['photo']) }}" alt="">
                                        @else
                                            <span class="rs-avatar rs-avatar--sm" aria-hidden="true">{{ Proto::initials($headName) }}</span>
                                        @endif
                                    </span>
                                    <span class="rs-sector-head-text">
                                        <span class="rs-sector-head-name">{{ $headName }}</span>
                                        <span class="rs-sector-head-role">@lang('staff.head_of_sector')</span>
                                    </span>
                                </span>
                            @endif

                            <span class="rs-sector-card-foot">
                                <span class="rs-stack" aria-hidden="true">
                                    @foreach ($preview as $person)
                                        @php($personName = Proto::text($person['name']))
                                        <span class="rs-stack-item" title="{{ $personName }}">
                                            @if ($person['photo'])
                                                <img src="{{ asset($person['photo']) }}" alt="">
                                            @else
                                                <span class="rs-avatar rs-avatar--stack">{{ Proto::initials($personName) }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if ($sector['staff']->count() > $preview->count())
                                        <span class="rs-stack-item rs-stack-more">+{{ $sector['staff']->count() - $preview->count() }}</span>
                                    @endif
                                </span>
                                <span class="rs-sector-members">{{ trans_choice('staff.members', $members, ['count' => $members]) }}</span>
                                <span class="rs-see-more">@lang('staff.see_more') <i class="fa-solid fa-arrow-right"></i></span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</div>
