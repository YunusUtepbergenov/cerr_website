<div>
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <div class="subtitle">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
                <div class="label"><i class="fa-solid fa-newspaper me-1"></i> Total news</div>
                <div class="value">{{ number_format($newsCount) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
                <div class="label"><i class="fa-solid fa-circle-check me-1"></i> Published</div>
                <div class="value success">{{ number_format($publishedCount) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
                <div class="label"><i class="fa-regular fa-pen-to-square me-1"></i> Drafts</div>
                <div class="value muted">{{ number_format($draftCount) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
                <div class="label"><i class="fa-solid fa-folder-open me-1"></i> Categories / Tags</div>
                <div class="value">{{ $categoryCount }} <span class="text-muted" style="font-size: 1rem;">/ {{ $tagCount }}</span></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent news</span>
            <a href="{{ route('admin.news.index') }}" class="btn btn-sm btn-outline-primary">
                All news <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Title</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 160px;">Languages</th>
                        <th style="width: 130px;">Updated</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentNews as $item)
                        @php $availableLocales = $item->translations->pluck('lang')->all(); @endphp
                        <tr>
                            <td class="text-muted small">#{{ $item->id }}</td>
                            <td><div class="fw-semibold">{{ optional($item->translations->first())->title ?? '—' }}</div></td>
                            <td><span class="pill status-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                            <td>
                                @foreach (['kr', 'uz', 'ru', 'en'] as $loc)
                                    <span class="lang-chip {{ in_array($loc, $availableLocales, true) ? '' : 'missing' }}">{{ $loc }}</span>
                                @endforeach
                            </td>
                            <td><span class="text-muted small">{{ $item->updated_at?->diffForHumans() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-regular fa-newspaper d-block"></i>
                                    <div class="fw-semibold">No news yet.</div>
                                    <div class="small mt-1"><a href="{{ route('admin.news.create') }}">Create your first article</a>.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
