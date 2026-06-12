<div>
    <x-admin.page-header :title="__('admin.dashboard.title')" :subtitle="__('admin.dashboard.welcome', ['name' => explode(' ', auth()->user()->name)[0]])">
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}
        </a>
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-photo-film me-1"></i> {{ __('admin.dashboard.upload_media') }}
        </a>
    </x-admin.page-header>

    <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-xl-5">
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.total_news')" :value="number_format($newsCount)" icon="fa-solid fa-newspaper" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.published')" :value="number_format($publishedCount)" icon="fa-solid fa-circle-check" accent="success" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.drafts')" :value="number_format($draftCount)" icon="fa-regular fa-pen-to-square" accent="warning" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.categories')" :value="$categoryCount" icon="fa-solid fa-folder-open" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.tags')" :value="$tagCount" icon="fa-solid fa-tags" />
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('admin.dashboard.recent_news') }}</span>
            <a href="{{ route('admin.news.index') }}" class="btn btn-sm btn-outline-primary">
                {{ __('admin.dashboard.all_news') }} <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>{{ __('admin.news.title') }}</th>
                        <th style="width: 140px;">{{ __('admin.common.status') }}</th>
                        <th style="width: 150px;">{{ __('admin.common.languages') }}</th>
                        <th style="width: 140px;">{{ __('admin.common.updated') }}</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentNews as $item)
                        <tr>
                            <td class="text-muted small">#{{ $item->id }}</td>
                            <td><div class="fw-semibold">{{ optional($item->translations->first())->title ?? '—' }}</div></td>
                            <td><x-admin.status-pill :status="$item->status" /></td>
                            <td><x-admin.lang-chips :available="$item->translations->pluck('lang')->all()" /></td>
                            <td><span class="text-muted small">{{ ($item->updated_at ?? $item->created_at)?->diffForHumans() ?? '—' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state icon="fa-regular fa-newspaper" :title="__('admin.dashboard.no_news')">
                                    <a href="{{ route('admin.news.create') }}">{{ __('admin.dashboard.create_first') }}</a>.
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->canViewActivity())
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('admin.dashboard.recent_activity') }}</span>
                <a href="{{ route('admin.activity.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('admin.dashboard.all_activity') }} <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body py-2">
                @forelse ($recentActivity as $a)
                    <div class="activity-row d-flex gap-2 align-items-start py-2">
                        <i class="fa-solid fa-clock-rotate-left text-muted small mt-1"></i>
                        <div class="flex-grow-1">
                            <span class="small">
                                <strong>{{ $a->user->name ?? __('admin.activity.system') }}</strong>
                                {{ __('admin.activity.action_'.$a->action) }}
                                <span class="text-muted">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</span>
                            </span>
                        </div>
                        <span class="text-muted small">{{ $a->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-clock" :title="__('admin.activity.no_activity')" class="py-3" />
                @endforelse
            </div>
        </div>
    @endif
</div>
