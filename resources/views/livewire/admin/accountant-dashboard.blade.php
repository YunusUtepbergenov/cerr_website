<div>
    <x-admin.page-header :title="__('admin.accountant.title')" :subtitle="__('admin.accountant.welcome', ['name' => explode(' ', auth()->user()->name)[0]])">
        <a href="{{ route('admin.open-data.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-database me-1"></i> {{ __('admin.accountant.manage_data') }}
        </a>
    </x-admin.page-header>

    <section class="kpi-bento kpi-quad mb-4">
        <x-admin.stat-card :label="__('admin.accountant.total_datasets')" :value="number_format($totalCount)" icon="fa-solid fa-database" accent="violet" />
        <x-admin.stat-card :label="__('admin.accountant.published')" :value="number_format($publishedCount)" icon="fa-solid fa-circle-check" accent="success" />
        <x-admin.stat-card :label="__('admin.accountant.drafts')" :value="number_format($draftCount)" icon="fa-regular fa-pen-to-square" accent="warning" />
        <x-admin.stat-card :label="__('admin.accountant.downloads')" :value="number_format($totalDownloads)" icon="fa-solid fa-download" accent="info" />
    </section>

    <div class="dash-grid single">
        <div class="card news-panel">
            <div class="panel-head">
                <div>
                    <h3>{{ __('admin.accountant.recent') }}</h3>
                    <div class="panel-sub">{{ __('admin.accountant.recent_sub') }}</div>
                </div>
                <a href="{{ route('admin.open-data.index') }}" class="link-all">
                    {{ __('admin.accountant.all_data') }} <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="news-list">
                @forelse ($recentEntries as $entry)
                    <a href="{{ route('admin.open-data.index') }}" class="news-row">
                        <span class="news-id">#{{ $entry->id }}</span>
                        <span class="news-body">
                            <span class="news-title">{{ $entry->title() ?: '—' }}</span>
                            <span class="news-meta">
                                <x-admin.status-pill :status="$entry->is_published ? 'published' : 'draft'" />
                                <span class="lang-chip">{{ $entry->year }}@if ($entry->quarter) · {{ $entry->quarterLabel() }}@endif</span>
                                <span class="lang-chip">{{ $entry->fileExtension() }}</span>
                                @php $entryTime = $entry->updated_at ?? $entry->created_at; @endphp
                                @if ($entryTime)
                                    <span class="news-time"><i class="fa-regular fa-clock"></i> {{ $entryTime->diffForHumans() }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="news-edit" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-folder-open" :title="__('admin.accountant.no_data')">
                        <a href="{{ route('admin.open-data.index') }}">{{ __('admin.accountant.add_first') }}</a>.
                    </x-admin.empty-state>
                @endforelse
            </div>
        </div>
    </div>
</div>
