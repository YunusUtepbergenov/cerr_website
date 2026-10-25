@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    @php($position = __('staff.position_'.$person['position']))
    @php($sectorName = __('structure.'.$sector['key']))

    <section class="rs-page rs-profile" style="--rs-accent: {{ $sector['accent'] }}">
        <div class="rs-wrap">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <a href="{{ route('research-staff.index') }}" wire:navigate>@lang('staff.title')</a>
                <span class="sep">/</span>
                <span class="current">{{ $name }}</span>
            </nav>

            <header class="rs-profile-hero">
                <div class="rs-profile-photo">
                    @if ($person['photo'])
                        <img src="{{ asset($person['photo']) }}" alt="{{ $name }}">
                    @else
                        <span class="rs-avatar" aria-hidden="true">{{ Proto::initials($name) }}</span>
                    @endif
                </div>
                <div class="rs-profile-main">
                    <a class="rs-role-tag" href="{{ route('research-staff.index') }}#sector-{{ $sector['slug'] }}" wire:navigate>
                        <span class="rs-pill-dot"></span>{{ __('staff.sector_short_'.$sector['key']) }}
                    </a>
                    <h1 class="rs-profile-name">{{ $name }}</h1>
                    <p class="rs-profile-pos">
                        {{ $position }}
                        @if ($person['degree'])
                            <span class="rs-degree">{{ __('staff.degree_'.$person['degree']) }}</span>
                        @endif
                    </p>
                    <ul class="rs-contact-row rs-contact-row--lg">
                        <li><i class="fa-regular fa-envelope"></i><a href="mailto:{{ $person['email'] }}">{{ $person['email'] }}</a></li>
                        @if ($person['phone'])
                            <li><i class="fa-solid fa-phone"></i><a href="tel:{{ preg_replace('/[^+\d]/', '', $person['phone']) }}">{{ $person['phone'] }}</a></li>
                        @endif
                    </ul>
                </div>
                <dl class="rs-facts">
                    <div><dt>@lang('staff.sector')</dt><dd>{{ $sectorName }}</dd></div>
                    @if ($person['since'])
                        <div><dt>CERR</dt><dd>@lang('staff.since', ['year' => $person['since']])</dd></div>
                    @endif
                    <div><dt>@lang('staff.languages')</dt><dd>{{ Proto::text($person['languages']) }}</dd></div>
                </dl>
            </header>

            <p class="rs-note"><i class="fa-solid fa-flask"></i> @lang('staff.prototype_note')</p>

            <div class="rs-profile-layout">
                <div class="rs-profile-body">
                    @if (Proto::text($person['about']) !== '')
                        <section class="rs-block">
                            <h2 class="rs-block-title">@lang('staff.about')</h2>
                            <p class="rs-prose">{{ Proto::text($person['about']) }}</p>
                        </section>
                    @endif

                    @php($interests = $person['interests'][app()->getLocale()] ?? $person['interests'][in_array(app()->getLocale(), ['uz', 'en']) ? 'en' : 'ru'] ?? [])
                    @if ($interests)
                        <section class="rs-block">
                            <h2 class="rs-block-title">@lang('staff.interests')</h2>
                            <ul class="rs-chips">
                                @foreach ($interests as $interest)
                                    <li>{{ $interest }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @foreach (['education', 'experience'] as $section)
                        @if ($person[$section])
                            <section class="rs-block">
                                <h2 class="rs-block-title">@lang('staff.'.$section)</h2>
                                <ol class="rs-timeline">
                                    @foreach ($person[$section] as $entry)
                                        <li>
                                            <span class="rs-years">{{ $entry['years'] }}</span>
                                            <span class="rs-entry">{{ Proto::text($entry['text']) }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endif
                    @endforeach

                    @if ($person['publications'])
                        <section class="rs-block">
                            <h2 class="rs-block-title">@lang('staff.publications')</h2>
                            <ol class="rs-pubs">
                                @foreach ($person['publications'] as $publication)
                                    <li>{{ $publication }}</li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                </div>

                <aside class="rs-profile-side">
                    <section class="rs-side-card">
                        <h2 class="rs-side-title">@lang('staff.sector')</h2>
                        <p class="rs-side-sector">{{ $sectorName }}</p>
                        <p class="rs-side-desc">{{ __('staff.sector_desc_'.$sector['key']) }}</p>
                        <a class="rs-more" href="{{ route('research-staff.index') }}#sector-{{ $sector['slug'] }}" wire:navigate>@lang('staff.all_in_sector') <i class="fa-solid fa-arrow-right"></i></a>
                    </section>

                    @if ($colleagues->isNotEmpty())
                        <section class="rs-side-card">
                            <h2 class="rs-side-title">@lang('staff.colleagues')</h2>
                            <ul class="rs-colleagues">
                                @foreach ($colleagues as $colleague)
                                    @php($colleagueName = Proto::text($colleague['name']))
                                    <li>
                                        <a href="{{ route('research-staff.show', $colleague['slug']) }}" wire:navigate>
                                            <span class="rs-avatar rs-avatar--sm" aria-hidden="true">{{ Proto::initials($colleagueName) }}</span>
                                            <span class="rs-colleague-text">
                                                <span class="rs-colleague-name">{{ $colleagueName }}</span>
                                                <span class="rs-colleague-pos">{{ __('staff.position_'.$colleague['position']) }}</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <a class="rs-back" href="{{ route('research-staff.index') }}" wire:navigate><i class="fa-solid fa-arrow-left"></i> @lang('staff.back')</a>
                </aside>
            </div>
        </div>
    </section>
</div>
