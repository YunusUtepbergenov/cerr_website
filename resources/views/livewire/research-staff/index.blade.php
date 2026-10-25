@use('App\Support\ResearchStaffPrototype', 'Proto')

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/research-staff.css') }}?v={{ filemtime(public_path('css/research-staff.css')) }}">
    @endpush

    <section class="rs-page" x-data="staffDirectory('{{ $sectors->first()['slug'] }}')" x-on:hashchange.window="fromHash()">
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

            <div class="rs-directory">
                <aside class="rs-index">
                    <label class="rs-search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" x-model="q" x-on:input="filter()" placeholder="@lang('staff.search')" autocomplete="off">
                    </label>

                    <ol class="rs-index-list" role="tablist" aria-label="@lang('staff.sector')">
                        @foreach ($sectors as $index => $sector)
                            @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
                            <li style="--rs-accent: {{ $sector['accent'] }}">
                                <button type="button" class="rs-index-item" role="tab" data-index="{{ $sector['slug'] }}"
                                    x-on:click="select('{{ $sector['slug'] }}')"
                                    :aria-selected="! searching && active === '{{ $sector['slug'] }}' ? 'true' : 'false'"
                                    :class="{ 'is-dim': searching && ! hits['{{ $sector['slug'] }}'] }">
                                    <span class="rs-index-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="rs-index-name">{{ __('staff.sector_short_'.$sector['key']) }}</span>
                                    <span class="rs-index-count" x-text="searching ? (hits['{{ $sector['slug'] }}'] || 0) : {{ $members }}">{{ $members }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </aside>

                <div class="rs-pane">
                    <p class="rs-found" x-show="searching" x-cloak>
                        <span x-text="shown === 1 ? @js(trans_choice('staff.found', 1, ['count' => 1])) : @js(__('staff.found_many')).replace(':count', shown)"></span>
                        <button type="button" class="rs-clear" x-on:click="q = ''; filter()">@lang('staff.clear') <i class="fa-solid fa-xmark"></i></button>
                    </p>

                    @foreach ($sectors as $index => $sector)
                        @php($members = $sector['staff']->count() + ($sector['head'] ? 1 : 0))
                        <section class="rs-sector" id="sector-{{ $sector['slug'] }}" role="tabpanel" style="--rs-accent: {{ $sector['accent'] }}" data-sector="{{ $sector['slug'] }}"
                            x-show="searching ? hits['{{ $sector['slug'] }}'] > 0 : active === '{{ $sector['slug'] }}'" @if (! $loop->first) x-cloak @endif>
                            <header class="rs-sector-head">
                                <span class="rs-sector-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="rs-sector-text">
                                    <h2 class="rs-sector-title">{{ Proto::sectorName($sector['key']) }}</h2>
                                    <p class="rs-sector-desc">{{ __('staff.sector_desc_'.$sector['key']) }}</p>
                                </div>
                                <span class="rs-sector-count">{{ trans_choice('staff.members', $members, ['count' => $members]) }}</span>
                            </header>

                            @if ($sector['head'])
                                @php($head = $sector['head'])
                                @php($headName = Proto::text($head['name']))
                                <article class="rs-lead-card" data-person data-search="{{ Str::lower(implode(' ', [$head['name']['ru'], $head['name']['en'], __('staff.position_'.$head['position']), $head['email']])) }}">
                                    <div class="rs-lead-photo">
                                        @if ($head['photo'])
                                            <img src="{{ asset($head['photo']) }}" alt="{{ $headName }}">
                                        @else
                                            <span class="rs-avatar" aria-hidden="true">{{ Proto::initials($headName) }}</span>
                                        @endif
                                    </div>
                                    <div class="rs-lead-body">
                                        <span class="rs-role-tag">@lang('staff.head_of_sector')</span>
                                        <h3 class="rs-lead-name">
                                            <a href="{{ route('research-staff.show', $head['slug']) }}" wire:navigate class="rs-stretch">{{ $headName }}</a>
                                            @if ($head['degree'])
                                                <span class="rs-degree">{{ __('staff.degree_'.$head['degree']) }}</span>
                                            @endif
                                        </h3>
                                        @if ($areas = Proto::items($head['research_areas']))
                                            <p class="rs-lead-areas">{{ implode(' · ', $areas) }}</p>
                                        @endif
                                        <ul class="rs-contact-row">
                                            <li><i class="fa-regular fa-envelope"></i><a href="mailto:{{ $head['email'] }}">{{ $head['email'] }}</a></li>
                                            @if ($head['phone'])
                                                <li><i class="fa-solid fa-phone"></i><a href="tel:{{ preg_replace('/[^+\d]/', '', $head['phone']) }}">{{ $head['phone'] }}</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                    <span class="rs-lead-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                                </article>
                            @endif

                            @if ($sector['staff']->isNotEmpty())
                                <ul class="rs-list">
                                    @foreach ($sector['staff'] as $person)
                                        @php($name = Proto::text($person['name']))
                                        <li class="rs-row" data-person data-search="{{ Str::lower(implode(' ', [$person['name']['ru'], $person['name']['en'], __('staff.position_'.$person['position']), $person['email']])) }}">
                                            <span class="rs-row-photo">
                                                @if ($person['photo'])
                                                    <img src="{{ asset($person['photo']) }}" alt="{{ $name }}">
                                                @else
                                                    <span class="rs-avatar rs-avatar--row" aria-hidden="true">{{ Proto::initials($name) }}</span>
                                                @endif
                                            </span>
                                            <span class="rs-row-text">
                                                <a href="{{ route('research-staff.show', $person['slug']) }}" wire:navigate class="rs-stretch rs-row-name">{{ $name }}</a>
                                                <span class="rs-row-pos">
                                                    {{ __('staff.position_'.$person['position']) }}
                                                    @if ($person['degree'])
                                                        <span class="rs-degree">{{ __('staff.degree_'.$person['degree']) }}</span>
                                                    @endif
                                                </span>
                                            </span>
                                            <a class="rs-mail" href="mailto:{{ $person['email'] }}" title="{{ $person['email'] }}" aria-label="{{ __('staff.email') }}: {{ $person['email'] }}"><i class="fa-regular fa-envelope"></i></a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach

                    <p class="rs-empty" x-show="searching && shown === 0" x-cloak>
                        <i class="fa-regular fa-face-meh"></i> @lang('staff.nothing_found')
                    </p>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function staffDirectory(firstSlug) {
                return {
                    active: firstSlug,
                    q: '',
                    searching: false,
                    shown: 0,
                    hits: {},
                    init() {
                        this.fromHash();
                    },
                    slugs() {
                        return [...this.$root.querySelectorAll('[data-sector]')].map((s) => s.dataset.sector);
                    },
                    fromHash() {
                        const slug = (location.hash || '').replace(/^#sector-/, '');
                        if (slug && this.slugs().includes(slug)) {
                            this.active = slug;
                            this.$nextTick(() => this.$root.querySelector('[data-index="' + slug + '"]')?.scrollIntoView({ block: 'nearest', inline: 'nearest' }));
                        }
                    },
                    select(slug) {
                        this.active = slug;
                        if (this.searching) { this.q = ''; this.filter(); }
                        history.replaceState(null, '', '#sector-' + slug);
                        const pane = this.$root.querySelector('.rs-pane');
                        if (pane && pane.getBoundingClientRect().top < 0) { pane.scrollIntoView({ block: 'start', behavior: 'smooth' }); }
                    },
                    filter() {
                        const q = this.q.trim().toLowerCase();
                        this.searching = q !== '';
                        let total = 0;
                        const hits = {};
                        this.$root.querySelectorAll('[data-sector]').forEach((section) => {
                            let count = 0;
                            section.querySelectorAll('[data-person]').forEach((card) => {
                                const hit = ! q || card.dataset.search.includes(q);
                                card.hidden = ! hit;
                                if (hit) { count++; }
                            });
                            hits[section.dataset.sector] = count;
                            total += count;
                        });
                        this.hits = hits;
                        this.shown = total;
                    },
                };
            }
        </script>
    @endpush
</div>
