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

            <div class="rs-layout">
                <div class="rs-main">
                    <ul class="rs-sector-grid">
                        @foreach ($sectors as $sector)
                            @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
                            @php($preview = collect([$sector['head']])->filter()->concat($sector['staff'])->take(3))
                            <li style="--rs-accent: {{ $sector['accent'] }}">
                                <a class="rs-sector-card" href="{{ route('research-staff.sector', $sector['slug']) }}" wire:navigate data-sector-card="{{ $sector['slug'] }}">
                                    <span class="rs-sector-card-head">
                                        <h2 class="rs-sector-card-title">{{ Proto::sectorName($sector['key']) }}</h2>
                                    </span>
                                    <span class="rs-sector-card-foot">
                                        <span class="rs-stack" aria-label="{{ trans_choice('staff.members', $members, ['count' => $members]) }}">
                                            @foreach ($preview as $person)
                                                @php($personName = Proto::text($person['name']))
                                                <span class="rs-stack-item" title="{{ $personName }}">
                                                    @if ($person['photo'])
                                                        <img src="{{ asset($person['photo']) }}" alt="">
                                                    @else
                                                        <span class="rs-avatar rs-avatar--stack" aria-hidden="true">{{ Proto::initials($personName) }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if ($members > $preview->count())
                                                <span class="rs-stack-item rs-stack-more">+{{ $members - $preview->count() }}</span>
                                            @endif
                                        </span>
                                        <span class="rs-see-more">@lang('staff.see_more') <i class="fa-solid fa-arrow-right"></i></span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-research-staff.about-nav active="staff" />
            </div>
        </div>
    </section>
</div>
