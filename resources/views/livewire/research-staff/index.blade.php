@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    <section class="rs-page" x-data="staffDirectory()">
        <div class="rs-wrap">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">@lang('staff.title')</span>
            </nav>

            <header class="rs-hero">
                <p class="rs-eyebrow">@lang('staff.eyebrow')</p>
                <h1 class="rs-title">@lang('staff.title')</h1>
                <p class="rs-lead">@lang('staff.lead')</p>
                <ul class="rs-stats" aria-label="@lang('staff.title')">
                    <li><strong>{{ $sectors->count() }}</strong><span>{{ trans_choice('staff.sectors', $sectors->count(), ['count' => '']) }}</span></li>
                    <li><strong>{{ $totalPeople }}</strong><span>{{ trans_choice('staff.researchers', $totalPeople, ['count' => '']) }}</span></li>
                    <li><strong>{{ $withDegree }}</strong><span>{{ trans_choice('staff.with_degree', $withDegree, ['count' => '']) }}</span></li>
                </ul>
            </header>

            <p class="rs-note"><i class="fa-solid fa-flask"></i> @lang('staff.prototype_note')</p>
        </div>

        <div class="rs-sentinel" x-ref="sentinel" aria-hidden="true"></div>
        <div class="rs-toolbar" x-ref="toolbar" :class="{ 'is-stuck': stuck }">
            <div class="rs-wrap rs-toolbar-inner">
                <nav class="rs-pills" aria-label="@lang('staff.sector')">
                    @foreach ($sectors as $sector)
                        <a href="#sector-{{ $sector['slug'] }}" class="rs-pill" style="--rs-accent: {{ $sector['accent'] }}" data-pill="{{ $sector['slug'] }}">
                            <span class="rs-pill-dot"></span>{{ __('staff.sector_short_'.$sector['key']) }}
                        </a>
                    @endforeach
                </nav>
                <label class="rs-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" x-model="q" x-on:input="filter()" placeholder="@lang('staff.search')" autocomplete="off">
                </label>
            </div>
        </div>

        <div class="rs-wrap">
            @foreach ($sectors as $index => $sector)
                @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
                <section class="rs-sector" id="sector-{{ $sector['slug'] }}" style="--rs-accent: {{ $sector['accent'] }}" data-sector="{{ $sector['slug'] }}">
                    <header class="rs-sector-head">
                        <span class="rs-sector-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="rs-sector-text">
                            <h2 class="rs-sector-title">{{ __('structure.'.$sector['key']) }}</h2>
                            <p class="rs-sector-desc">{{ __('staff.sector_desc_'.$sector['key']) }}</p>
                        </div>
                        <span class="rs-sector-count">{{ trans_choice('staff.members', $members, ['count' => $members]) }}</span>
                    </header>

                    @if ($sector['head'])
                        @php($head = $sector['head'])
                        @php($headName = Proto::text($head['name']))
                        <article class="rs-head-card" data-person data-search="{{ Str::lower(implode(' ', [$head['name']['ru'], $head['name']['en'], __('staff.position_'.$head['position']), $head['email']])) }}">
                            <div class="rs-head-photo">
                                @if ($head['photo'])
                                    <img src="{{ asset($head['photo']) }}" alt="{{ $headName }}">
                                @else
                                    <span class="rs-avatar" aria-hidden="true">{{ Proto::initials($headName) }}</span>
                                @endif
                            </div>
                            <div class="rs-head-body">
                                <span class="rs-role-tag">@lang('staff.head_of_sector')</span>
                                <h3 class="rs-head-name"><a href="{{ route('research-staff.show', $head['slug']) }}" wire:navigate class="rs-stretch">{{ $headName }}</a></h3>
                                @if ($head['degree'])
                                    <span class="rs-degree">{{ __('staff.degree_'.$head['degree']) }}</span>
                                @endif
                                <p class="rs-head-about">{{ Proto::text($head['about']) }}</p>
                                <ul class="rs-contact-row">
                                    <li><i class="fa-regular fa-envelope"></i><a href="mailto:{{ $head['email'] }}">{{ $head['email'] }}</a></li>
                                    @if ($head['phone'])
                                        <li><i class="fa-solid fa-phone"></i><a href="tel:{{ preg_replace('/[^+\d]/', '', $head['phone']) }}">{{ $head['phone'] }}</a></li>
                                    @endif
                                </ul>
                                <span class="rs-more">@lang('staff.profile') <i class="fa-solid fa-arrow-right"></i></span>
                            </div>
                        </article>
                    @endif

                    <div class="rs-grid">
                        @foreach ($sector['staff'] as $person)
                            @php($name = Proto::text($person['name']))
                            <article class="rs-card" data-person data-search="{{ Str::lower(implode(' ', [$person['name']['ru'], $person['name']['en'], __('staff.position_'.$person['position']), $person['email']])) }}">
                                <div class="rs-card-photo">
                                    @if ($person['photo'])
                                        <img src="{{ asset($person['photo']) }}" alt="{{ $name }}">
                                    @else
                                        <span class="rs-avatar" aria-hidden="true">{{ Proto::initials($name) }}</span>
                                    @endif
                                </div>
                                <div class="rs-card-body">
                                    <h3 class="rs-card-name"><a href="{{ route('research-staff.show', $person['slug']) }}" wire:navigate class="rs-stretch">{{ $name }}</a></h3>
                                    <p class="rs-card-pos">{{ __('staff.position_'.$person['position']) }}</p>
                                    <div class="rs-card-foot">
                                        @if ($person['degree'])
                                            <span class="rs-degree">{{ __('staff.degree_'.$person['degree']) }}</span>
                                        @endif
                                        <a class="rs-mail" href="mailto:{{ $person['email'] }}" title="{{ $person['email'] }}" aria-label="{{ __('staff.email') }}: {{ $person['email'] }}"><i class="fa-regular fa-envelope"></i></a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <p class="rs-empty" x-show="shown === 0" x-cloak>
                <i class="fa-regular fa-face-meh"></i> @lang('staff.nothing_found')
            </p>
        </div>
    </section>

    @push('scripts')
        <script>
            function staffDirectory() {
                return {
                    q: '',
                    shown: {{ $totalPeople }},
                    stuck: false,
                    init() {
                        this.pinToolbar();
                        this.watchSections();
                    },
                    // The theme's <body> scrolls, so position: sticky never engages; pin the bar below the fixed site header manually.
                    pinToolbar() {
                        const header = document.querySelector('.echo-header-area');
                        const update = () => {
                            const headerHeight = header && getComputedStyle(header).position === 'fixed' ? header.getBoundingClientRect().height : 0;
                            const stuck = this.$refs.sentinel.getBoundingClientRect().top <= headerHeight;
                            this.$refs.toolbar.style.setProperty('--rs-offset', headerHeight + 'px');
                            this.$refs.sentinel.style.height = stuck ? this.$refs.toolbar.offsetHeight + 'px' : '0px';
                            this.stuck = stuck;
                        };
                        window.addEventListener('scroll', update, { passive: true });
                        window.addEventListener('resize', update);
                        update();
                    },
                    watchSections() {
                        const pills = [...this.$root.querySelectorAll('[data-pill]')];
                        const sections = [...this.$root.querySelectorAll('[data-sector]')];
                        if (! ('IntersectionObserver' in window) || ! sections.length) { return; }
                        const visible = new Map();
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((e) => visible.set(e.target.dataset.sector, e.isIntersecting ? e.intersectionRatio : 0));
                            const best = sections.map((s) => s.dataset.sector).find((slug) => (visible.get(slug) || 0) > 0);
                            pills.forEach((p) => p.toggleAttribute('aria-current', p.dataset.pill === best));
                        }, { rootMargin: '-45% 0px -45% 0px', threshold: [0, .01, .5] });
                        sections.forEach((s) => observer.observe(s));
                    },
                    filter() {
                        const q = this.q.trim().toLowerCase();
                        let total = 0;
                        this.$root.querySelectorAll('[data-sector]').forEach((section) => {
                            let hits = 0;
                            section.querySelectorAll('[data-person]').forEach((card) => {
                                const hit = ! q || card.dataset.search.includes(q);
                                card.hidden = ! hit;
                                if (hit) { hits++; }
                            });
                            section.hidden = hits === 0;
                            total += hits;
                        });
                        this.shown = total;
                    },
                };
            }
        </script>
    @endpush
</div>
