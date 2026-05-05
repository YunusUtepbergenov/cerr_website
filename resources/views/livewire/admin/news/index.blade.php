<div>
    <div class="page-header">
        <div>
            <h1>News</h1>
            <div class="subtitle">Manage articles, translations, and publishing status</div>
        </div>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> New article
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute" style="left: .8rem; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control ps-5" placeholder="Search by title or slug…">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="auto_publish">Auto publish</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="category" class="form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ optional($cat->translations->firstWhere('language', app()->getLocale()))->name ?? optional($cat->translations->first())->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Title</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 160px;">Languages</th>
                        <th style="width: 160px;">Category</th>
                        <th style="width: 90px;">Views</th>
                        <th style="width: 130px;">Updated</th>
                        <th style="width: 130px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($newsList as $item)
                        @php
                            $translation = $item->translations->firstWhere('lang', app()->getLocale()) ?? $item->translations->first();
                            $catName = optional(optional($item->category)->translations->firstWhere('language', app()->getLocale()))->name
                                ?? optional(optional($item->category)->translations->first())->name
                                ?? '—';
                            $thumbUrl = $translation?->coverUrl();
                            $availableLocales = $item->translations->pluck('lang')->all();
                        @endphp
                        <tr wire:key="news-{{ $item->id }}">
                            <td>
                                @if ($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border-soft);">
                                @else
                                    <div style="width: 44px; height: 44px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #cbd5e1;"><i class="fa-regular fa-image"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $translation->title ?? '—' }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 360px;">{{ $item->slug }}</div>
                            </td>
                            <td><span class="pill status-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                            <td>
                                @foreach (['kr', 'uz', 'ru', 'en'] as $loc)
                                    <span class="lang-chip {{ in_array($loc, $availableLocales, true) ? '' : 'missing' }}">{{ $loc }}</span>
                                @endforeach
                            </td>
                            <td><span class="text-truncate d-inline-block" style="max-width: 140px;">{{ $catName }}</span></td>
                            <td><span class="text-muted small"><i class="fa-regular fa-eye me-1"></i>{{ number_format($item->view_count) }}</span></td>
                            <td><span class="text-muted small">{{ $item->updated_at?->diffForHumans() }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('show.news', $item->slug) }}" target="_blank" class="btn btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="Delete this news item and all its translations?">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa-regular fa-newspaper d-block"></i>
                                    <div class="fw-semibold">No news matches the current filters.</div>
                                    <div class="small mt-1">Try clearing search and filter criteria, or <a href="{{ route('admin.news.create') }}">create a new article</a>.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($newsList->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $newsList->firstItem() }}–{{ $newsList->lastItem() }} of {{ $newsList->total() }}
                </div>
                <div>{{ $newsList->onEachSide(1)->links() }}</div>
            </div>
        @endif
    </div>
</div>
