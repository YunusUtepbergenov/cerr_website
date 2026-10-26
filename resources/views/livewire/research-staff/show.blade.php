@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    @php($position = __('staff.position_'.$person['position']))
    @php($sectorName = __('structure.'.$sector['key']))
    @php($areas = Proto::items($person['research_areas']))
    @php($profileLinks = array_filter($person['profiles']))
    @php($publicationCount = Proto::publicationCount($person))
    @php($hasEducation = collect(Proto::EDUCATION_FIELDS)->contains(fn ($field) => Proto::text($person[$field]) !== ''))
    @php($hasActivity = $areas !== [] || collect(Proto::ACTIVITY_FIELDS)->contains(fn ($field) => $person[$field] !== []))

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
                    <a class="rs-role-tag" href="{{ route('research-staff.sector', $sector['slug']) }}" wire:navigate>
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
                    @if ($profileLinks)
                        <ul class="rs-profiles" aria-label="@lang('staff.profiles')">
                            @foreach ($profileLinks as $network => $url)
                                <li>
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="rs-profile-link rs-profile-link--{{ $network }}">
                                        @if ($network === 'orcid')
                                            <i class="fab fa-orcid"></i>
                                        @elseif ($network === 'scholar')
                                            <i class="fas fa-graduation-cap"></i>
                                        @else
                                            <i class="fas fa-database"></i>
                                        @endif
                                        {{ __('staff.profile_'.$network) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <dl class="rs-facts">
                    <div><dt>@lang('staff.sector')</dt><dd>{{ $sectorName }}</dd></div>
                    @if (Proto::text($person['degree_title']) !== '')
                        <div><dt>@lang('staff.degree')</dt><dd>{{ Proto::text($person['degree_title']) }}</dd></div>
                    @endif
                    @if ($publicationCount > 0)
                        <div><dt>@lang('staff.publications')</dt><dd>{{ trans_choice('staff.publications_count', $publicationCount) }}</dd></div>
                    @endif
                </dl>
            </header>

            <div class="rs-profile-layout">
                <div class="rs-profile-body">
                    @if (! $hasEducation && ! $hasActivity && $publicationCount === 0)
                        <section class="rs-part rs-part--pending">
                            <header class="rs-part-head">
                                <span class="rs-part-num" aria-hidden="true"><i class="fa-regular fa-file-lines"></i></span>
                                <h2 class="rs-part-title">@lang('staff.profile')</h2>
                            </header>
                            <p class="rs-pending">@lang('staff.profile_pending')</p>
                            <ol class="rs-pending-parts">
                                <li><span>I</span>@lang('staff.part_education')</li>
                                <li><span>II</span>@lang('staff.part_research')</li>
                                <li><span>III</span>@lang('staff.part_publications')</li>
                            </ol>
                        </section>
                    @endif

                    @if ($hasEducation)
                        <section class="rs-part" id="education">
                            <header class="rs-part-head">
                                <span class="rs-part-num" aria-hidden="true">I</span>
                                <h2 class="rs-part-title">@lang('staff.part_education')</h2>
                            </header>
                            <dl class="rs-edu">
                                @foreach (Proto::EDUCATION_FIELDS as $field)
                                    @if (Proto::text($person[$field]) !== '')
                                        <div class="rs-edu-row">
                                            <dt>{{ __('staff.'.$field) }}</dt>
                                            <dd>{{ Proto::text($person[$field]) }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </section>
                    @endif

                    @if ($hasActivity)
                        <section class="rs-part" id="research">
                            <header class="rs-part-head">
                                <span class="rs-part-num" aria-hidden="true">II</span>
                                <h2 class="rs-part-title">@lang('staff.part_research')</h2>
                            </header>

                            @if ($areas)
                                <section class="rs-block">
                                    <h3 class="rs-block-title">@lang('staff.research_areas')</h3>
                                    <ul class="rs-chips">
                                        @foreach ($areas as $area)
                                            <li>{{ $area }}</li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif

                            @foreach (Proto::ACTIVITY_FIELDS as $field)
                                @if ($person[$field])
                                    <section class="rs-block">
                                        <h3 class="rs-block-title">@lang('staff.'.$field)</h3>
                                        <ol class="rs-timeline">
                                            @foreach ($person[$field] as $entry)
                                                <li>
                                                    <span class="rs-years">{{ $entry['years'] }}</span>
                                                    <span class="rs-entry">{{ Proto::text($entry['text']) }}</span>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </section>
                                @endif
                            @endforeach
                        </section>
                    @endif

                    @if ($publicationCount > 0)
                        <section class="rs-part" id="publications">
                            <header class="rs-part-head">
                                <span class="rs-part-num" aria-hidden="true">III</span>
                                <h2 class="rs-part-title">@lang('staff.part_publications')</h2>
                            </header>

                            @foreach (Proto::PUBLICATION_FIELDS as $field)
                                @if ($person[$field])
                                    <section class="rs-block">
                                        <h3 class="rs-block-title">{{ __('staff.'.$field) }} <span class="rs-block-count">{{ count($person[$field]) }}</span></h3>
                                        <ol class="rs-pubs">
                                            @foreach ($person[$field] as $publication)
                                                <li>
                                                    <span class="rs-pub-text">{{ Proto::text($publication['text']) }}</span>
                                                    @if (! empty($publication['url']))
                                                        <a class="rs-pub-link" href="{{ $publication['url'] }}" target="_blank" rel="noopener">
                                                            <i class="fas fa-external-link-alt"></i> {{ str_contains($publication['url'], 'doi.org') ? 'DOI' : __('staff.open_link') }}
                                                        </a>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ol>
                                    </section>
                                @endif
                            @endforeach
                        </section>
                    @endif
                </div>

                <aside class="rs-profile-side">
                    <section class="rs-side-card">
                        <h2 class="rs-side-title">@lang('staff.sector')</h2>
                        <p class="rs-side-sector">{{ $sectorName }}</p>
                        <p class="rs-side-desc">{{ __('staff.sector_desc_'.$sector['key']) }}</p>
                        <a class="rs-more" href="{{ route('research-staff.sector', $sector['slug']) }}" wire:navigate>@lang('staff.all_in_sector') <i class="fa-solid fa-arrow-right"></i></a>
                    </section>

                    @if ($hasEducation || $hasActivity || $publicationCount > 0)
                        <nav class="rs-side-card rs-side-nav" aria-label="@lang('staff.contents')">
                            <h2 class="rs-side-title">@lang('staff.contents')</h2>
                            <ol>
                                @if ($hasEducation)<li><a href="#education"><span>I</span>@lang('staff.part_education')</a></li>@endif
                                @if ($hasActivity)<li><a href="#research"><span>II</span>@lang('staff.part_research')</a></li>@endif
                                @if ($publicationCount > 0)<li><a href="#publications"><span>III</span>@lang('staff.part_publications')</a></li>@endif
                            </ol>
                        </nav>
                    @endif

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
