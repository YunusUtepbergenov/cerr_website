<div>
    @php
        $showActivity = auth()->user()->canViewActivity();
        $ringCirc = 194.78; // 2·π·31
        $ringDash = round($ringCirc * (1 - $publicationRate / 100), 1);

        // Activity dots take their colour + icon from the subject they touched,
        // with a few actions (delete / publish) overriding to a clearer signal.
        $subjectStyles = [
            'News' => ['feed-news', 'fa-newspaper'],
            'OpenData' => ['feed-data', 'fa-database'],
            'User' => ['feed-user', 'fa-users'],
            'Video' => ['feed-video', 'fa-video'],
            'Category' => ['feed-category', 'fa-folder-open'],
            'Tag' => ['feed-tag', 'fa-tags'],
            'Page' => ['feed-page', 'fa-file-lines'],
        ];
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

        <x-admin.stat-card :label="__('admin.dashboard.published')" :value="number_format($publishedCount)" icon="fa-solid fa-circle-check" accent="success" />
        <x-admin.stat-card :label="__('admin.dashboard.drafts')" :value="number_format($draftCount)" icon="fa-regular fa-pen-to-square" accent="warning" />
        <x-admin.stat-card :label="__('admin.dashboard.categories')" :value="$categoryCount" icon="fa-solid fa-folder-open" accent="info" />
        <x-admin.stat-card :label="__('admin.dashboard.tags')" :value="$tagCount" icon="fa-solid fa-tags" accent="violet" />
    </section>

    <div class="dash-grid {{ $showActivity ? '' : 'single' }}">
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
                            <span class="news-title">{{ optional($item->translations->first())->title ?? '—' }}</span>
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

        @if ($showActivity)
            <div class="card activity-panel">
                <div class="panel-head">
                    <div>
                        <h3>{{ __('admin.dashboard.recent_activity') }}</h3>
                        <div class="panel-sub">{{ __('admin.dashboard.recent_activity_sub') }}</div>
                    </div>
                    <a href="{{ route('admin.activity.index') }}" class="link-all">
                        {{ __('admin.dashboard.all_activity') }} <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
                <div class="feed">
                    @forelse ($recentActivity as $a)
                        @php
                            [$dotClass, $dotIcon] = $subjectStyles[class_basename($a->subject_type)] ?? ['feed-default', 'fa-clock-rotate-left'];
                            if ($a->action === 'deleted') { [$dotClass, $dotIcon] = ['feed-deleted', 'fa-trash-can']; }
                            elseif ($a->action === 'published') { [$dotClass, $dotIcon] = ['feed-published', 'fa-circle-check']; }
                            elseif ($a->action === 'unpublished') { [$dotClass, $dotIcon] = ['feed-unpublished', 'fa-eye-slash']; }
                            elseif ($a->action === 'reset_password') { $dotIcon = 'fa-key'; }
                            elseif ($a->action === 'updated') { $dotIcon = 'fa-pen'; }
                        @endphp
                        <div class="feed-item">
                            <span class="feed-dot {{ $dotClass }}">
                                <i class="fa-solid {{ $dotIcon }}"></i>
                            </span>
                            <div class="feed-txt">
                                <b>{{ $a->user->name ?? __('admin.activity.system') }}</b>
                                {{ __('admin.activity.action_'.$a->action) }}
                                <span class="feed-subject">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</span>
                                <span class="feed-when">{{ $a->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <x-admin.empty-state icon="fa-regular fa-clock" :title="__('admin.activity.no_activity')" />
                    @endforelse
                </div>

                @if (auth()->user()->canManageOpenData())
                    <a href="{{ route('admin.open-data.index') }}" class="mini-cta">
                        <span class="mc-icon"><i class="fa-solid fa-database"></i></span>
                        <span class="mc-text">
                            <b>{{ __('admin.nav.open_data') }}</b>
                            <small>{{ $pendingOpenData > 0 ? __('admin.dashboard.open_data_pending', ['count' => $pendingOpenData]) : __('admin.dashboard.open_data_manage') }}</small>
                        </span>
                        <span class="mc-go"><i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
