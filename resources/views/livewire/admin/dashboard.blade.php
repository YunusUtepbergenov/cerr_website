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
        </div>
        @php
            $sparkMax = max(1, max($sparkline ?: [0]));
            $sparkN = max(1, count($sparkline));
            $gap = 1.4;
            $barW = (100 - ($sparkN - 1) * $gap) / $sparkN;
        @endphp
        <div class="spark-wrap">
            <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="spark-svg" aria-hidden="true">
                @foreach ($sparkline as $i => $v)
                    @php
                        $barH = $v > 0 ? max(1.5, $v / $sparkMax * 40) : 0.6;
                        $x = round($i * ($barW + $gap), 2);
                    @endphp
                    <rect x="{{ $x }}" y="{{ round(40 - $barH, 2) }}" width="{{ round($barW, 2) }}" height="{{ round($barH, 2) }}" rx="0.5" />
                @endforeach
            </svg>
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
