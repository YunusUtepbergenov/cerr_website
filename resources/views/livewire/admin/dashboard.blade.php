<div>
    @php
        $ringCirc = 194.78; // 2·π·31
        $ringDash = round($ringCirc * (1 - $publicationRate / 100), 1);
        $locale = app()->getLocale();
    @endphp

    <x-admin.page-header :title="__('admin.dashboard.title')" :subtitle="__('admin.dashboard.welcome', ['name' => explode(' ', auth()->user()->name)[0]])">
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> {{ __('admin.dashboard.upload_media') }}
        </a>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-feather-pointed me-1"></i> {{ __('admin.news.new_article') }}
        </a>
    </x-admin.page-header>

    <section class="kpi-bento mb-4">
        <div class="kpi-feature">
            <div class="kf-head">
                <span class="kf-icon"><i class="fa-solid fa-newspaper"></i></span>
                <div class="kf-value">{{ number_format($newsCount) }}</div>
                <div class="kf-label">{{ __('admin.dashboard.total_in_system') }}</div>
            </div>
            <div class="kf-ringwrap">
                <svg class="kf-ring" width="76" height="76" viewBox="0 0 78 78" aria-hidden="true">
                    <circle cx="39" cy="39" r="31" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="10" />
                    <circle cx="39" cy="39" r="31" fill="none" stroke="url(#kf-grad)" stroke-width="10" stroke-linecap="round"
                        stroke-dasharray="{{ $ringCirc }}" stroke-dashoffset="{{ $ringDash }}" transform="rotate(-90 39 39)" />
                    <defs>
                        <linearGradient id="kf-grad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#8b5cf6" />
                            <stop offset="1" stop-color="#3ec9a7" />
                        </linearGradient>
                    </defs>
                    <text x="39" y="45" text-anchor="middle" font-family="Manrope, sans-serif" font-weight="800" font-size="18" fill="#fff">{{ $publicationRate }}%</text>
                </svg>
                <div class="kf-legend">
                    <span><i class="kf-dot" style="background:#8b5cf6"></i> {{ __('admin.dashboard.published') }} <b>{{ $publishedCount }}</b></span>
                    <span><i class="kf-dot" style="background:#f5a623"></i> {{ __('admin.dashboard.drafts') }} <b>{{ $draftCount }}</b></span>
                    <span><i class="kf-dot" style="background:rgba(255,255,255,.3)"></i> {{ __('admin.dashboard.publication_share') }} <b>{{ $publicationRate }}%</b></span>
                </div>
            </div>
        </div>

        <x-admin.stat-card :label="__('admin.dashboard.total_views')" :value="number_format($totalViews)" icon="fa-solid fa-eye" accent="primary" />
        <x-admin.stat-card :label="__('admin.dashboard.views_today')" :value="number_format($viewsToday)" icon="fa-regular fa-calendar-check" accent="info" />

        <x-admin.stat-card :label="__('admin.dashboard.views_7d')" :value="number_format($trend7d['current'])" icon="fa-solid fa-chart-line" accent="success">
            <x-slot:trend>
                <x-admin.trend-badge :change="$trend7d['change']" />
                <span class="stat-trend-sub">{{ __('admin.dashboard.vs_prev') }}</span>
            </x-slot:trend>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('admin.dashboard.views_30d')" :value="number_format($trend30d['current'])" icon="fa-solid fa-chart-column" accent="violet">
            <x-slot:trend>
                <x-admin.trend-badge :change="$trend30d['change']" />
                <span class="stat-trend-sub">{{ __('admin.dashboard.vs_prev') }}</span>
            </x-slot:trend>
        </x-admin.stat-card>
    </section>

    <div class="card spark-panel mb-4">
        <div class="panel-head">
            <div>
                <h3>{{ __('admin.dashboard.views_over_time') }}</h3>
                <div class="panel-sub">{{ __('admin.dashboard.views_over_time_sub') }}</div>
            </div>
            <div class="spark-stats">
                <div class="ss">
                    <div class="ss-val">{{ number_format($sparkMeta['total']) }}</div>
                    <div class="ss-label">{{ __('admin.dashboard.chart_total') }}</div>
                </div>
                <div class="ss">
                    <div class="ss-val">{{ number_format($sparkMeta['avg']) }}</div>
                    <div class="ss-label">{{ __('admin.dashboard.chart_avg') }}</div>
                </div>
                <div class="ss">
                    <div class="ss-val">{{ number_format($sparkMeta['peak']) }}</div>
                    <div class="ss-label">{{ __('admin.dashboard.chart_peak') }}</div>
                </div>
            </div>
        </div>
        @php
            $sparkN = max(1, count($sparkline));
            $sparkMax = max(1, $sparkMeta['niceMax']);
        @endphp
        <div class="spark-chart" x-data="{ tip: null }">
            <div class="sc-frame">
                <div class="sc-y" aria-hidden="true">
                    @foreach ($sparkMeta['ticks'] as $tick)
                        <span class="sc-ylabel" style="bottom: {{ round($tick / $sparkMax * 100, 2) }}%">{{ number_format($tick) }}</span>
                    @endforeach
                </div>
                <div class="sc-main">
                    @foreach ($sparkMeta['ticks'] as $tick)
                        <span class="sc-gridline {{ $tick === 0 ? 'is-baseline' : '' }}" style="bottom: {{ round($tick / $sparkMax * 100, 2) }}%" aria-hidden="true"></span>
                    @endforeach

                    @if ($sparkMeta['total'] === 0)
                        <div class="sc-empty">{{ __('admin.dashboard.no_view_data') }}</div>
                    @else
                        <div class="sc-cols">
                            @foreach ($sparkline as $i => $day)
                                @php $barPct = round($day['views'] / $sparkMax * 100, 2); @endphp
                                <div class="sc-col" tabindex="0" role="img"
                                     aria-label="{{ $day['label'] }} — {{ number_format($day['views']) }}"
                                     @mouseenter="tip = { label: '{{ $day['label'] }}', value: '{{ number_format($day['views']) }}', x: $el.offsetLeft + $el.offsetWidth / 2, h: {{ $barPct }} }"
                                     @focus="tip = { label: '{{ $day['label'] }}', value: '{{ number_format($day['views']) }}', x: $el.offsetLeft + $el.offsetWidth / 2, h: {{ $barPct }} }"
                                     @mouseleave="tip = null" @blur="tip = null">
                                    <span class="sc-bar" style="height: {{ $barPct }}%"></span>
                                </div>
                            @endforeach
                        </div>
                        @if ($sparkMeta['peakIndex'] >= 0)
                            <span class="sc-peak" x-show="!tip" style="left: clamp(18px, {{ round(($sparkMeta['peakIndex'] + .5) / $sparkN * 100, 2) }}%, calc(100% - 18px)); bottom: {{ round($sparkMeta['peak'] / $sparkMax * 100, 2) }}%">{{ number_format($sparkMeta['peak']) }}</span>
                        @endif
                    @endif

                    <div class="sc-tip" x-cloak x-show="tip" :style="tip && ('--tx: ' + tip.x + 'px; --th: ' + tip.h + '%')">
                        <b x-text="tip?.value"></b><span x-text="tip?.label"></span>
                    </div>
                </div>
            </div>
            <div class="sc-x" aria-hidden="true">
                @foreach ($sparkline as $i => $day)
                    @if ($i % 7 === 0)
                        <span class="sc-xlabel" style="left: {{ round(($i + .5) / $sparkN * 100, 2) }}%">{{ $day['label'] }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="card top-panel">
            <div class="panel-head">
                <div>
                    <h3>{{ __('admin.dashboard.top_viewed') }}</h3>
                    <div class="panel-sub">{{ __('admin.dashboard.top_viewed_sub') }}</div>
                </div>
            </div>
            <div class="period-tabs">
                @foreach (['day', 'week', 'month', 'year', 'all'] as $p)
                    <button type="button" wire:click="setPeriod('{{ $p }}')"
                            wire:loading.attr="disabled" wire:target="setPeriod"
                            class="period-tab {{ $topPeriod === $p ? 'active' : '' }}">
                        {{ __('admin.dashboard.period_'.$p) }}
                    </button>
                @endforeach
            </div>
            <div class="top-list" wire:loading.class="is-loading" wire:target="setPeriod">
                @forelse ($topNews as $i => $row)
                    @php
                        $t = $row['news']->translations->firstWhere('lang', $locale)
                            ?? $row['news']->translations->first();
                    @endphp
                    <a href="{{ route('admin.news.edit', $row['news']) }}" class="top-row">
                        <span class="top-rank">{{ $i + 1 }}</span>
                        <span class="top-body">
                            <span class="top-title">{{ $t->title ?? '#'.$row['news']->id }}</span>
                        </span>
                        <span class="top-views"><i class="fa-regular fa-eye"></i> {{ number_format($row['views']) }}</span>
                        @if ($topPeriod !== 'all')
                            <x-admin.trend-badge :change="$row['change']" />
                        @endif
                    </a>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-eye-slash" :title="__('admin.dashboard.no_view_data')" />
                @endforelse
            </div>
        </div>

        <div class="card news-panel">
            <div class="panel-head">
                <div>
                    <h3>{{ __('admin.dashboard.recent_news') }}</h3>
                    <div class="panel-sub">{{ __('admin.dashboard.recent_news_sub') }}</div>
                </div>
                <a href="{{ route('admin.news.index') }}" class="link-all">
                    {{ __('admin.dashboard.all_news') }} <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="news-list">
                @forelse ($recentNews as $item)
                    <a href="{{ route('admin.news.edit', $item) }}" class="news-row">
                        <span class="news-id">#{{ $item->id }}</span>
                        <span class="news-body">
                            <span class="news-title">{{ optional($item->translations->firstWhere('lang', $locale) ?? $item->translations->first())->title ?? '—' }}</span>
                            <span class="news-meta">
                                <x-admin.status-pill :status="$item->status" />
                                <x-admin.lang-chips :available="$item->translations->pluck('lang')->all()" />
                                @php $newsTime = $item->updated_at ?? $item->created_at; @endphp
                                @if ($newsTime)
                                    <span class="news-time"><i class="fa-regular fa-clock"></i> {{ $newsTime->diffForHumans() }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="news-edit" aria-hidden="true"><i class="fa-solid fa-pen"></i></span>
                    </a>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-newspaper" :title="__('admin.dashboard.no_news')">
                        <a href="{{ route('admin.news.create') }}">{{ __('admin.dashboard.create_first') }}</a>.
                    </x-admin.empty-state>
                @endforelse
            </div>
        </div>
    </div>
</div>
