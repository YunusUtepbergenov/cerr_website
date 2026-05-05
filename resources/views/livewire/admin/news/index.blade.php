<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.news.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.news.subtitle') }}</div>
        </div>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute" style="left: .8rem; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control ps-5" placeholder="{{ __('admin.news.search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="status" class="form-select">
                        <option value="">{{ __('admin.news.all_statuses') }}</option>
                        <option value="draft">{{ __('admin.news.status_short_draft') }}</option>
                        <option value="published">{{ __('admin.news.status_short_published') }}</option>
                        <option value="auto_publish">{{ __('admin.news.status_short_auto_publish') }}</option>
                        <option value="disabled">{{ __('admin.news.status_short_disabled') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="category" class="form-select">
                        <option value="">{{ __('admin.news.all_categories') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ optional($cat->translations->firstWhere('language', app()->getLocale()))->name ?? optional($cat->translations->first())->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div x-data x-show="$wire.selected.length > 0" x-cloak class="card mb-3 p-3 d-flex flex-row gap-2 align-items-center" style="background: #fff7ed; border-color: #fed7aa;">
        <span class="fw-semibold flex-grow-1">{{ __('admin.bulk.selected', ['count' => count($selected)]) }}</span>
        <button class="btn btn-sm btn-success" wire:click="bulkPublish"><i class="fa-solid fa-circle-check me-1"></i> {{ __('admin.bulk.publish') }}</button>
        <button class="btn btn-sm btn-secondary" wire:click="bulkUnpublish"><i class="fa-solid fa-eye-slash me-1"></i> {{ __('admin.bulk.unpublish') }}</button>
        <button class="btn btn-sm btn-danger" type="button" x-data
                @click="$dispatch('open-confirm', { message: @js(__('admin.bulk.confirm_delete', ['count' => count($selected)])), onConfirm: () => $wire.bulkDelete() })">
            <i class="fa-solid fa-trash me-1"></i> {{ __('admin.bulk.delete') }}
        </button>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 36px;"><input type="checkbox" wire:model.live="selectAll"></th>
                        <th style="width: 60px;"></th>
                        <th>{{ __('admin.news.title') }}</th>
                        <th style="width: 130px;">{{ __('admin.common.status') }}</th>
                        <th style="width: 160px;">{{ __('admin.common.languages') }}</th>
                        <th style="width: 160px;">{{ __('admin.news.category_label') }}</th>
                        <th style="width: 90px;">{{ __('admin.news.views') }}</th>
                        <th style="width: 130px;">{{ __('admin.common.updated') }}</th>
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
                            <td><input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
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
                                    <a href="{{ route('show.news', $item->slug) }}" target="_blank" class="btn btn-outline-secondary" title="{{ __('admin.common.view') }}"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-outline-primary" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></a>
                                    <button type="button" class="btn btn-outline-danger" title="{{ __('admin.common.delete') }}"
                                        x-data
                                        @click="$dispatch('open-confirm', { message: @js(__('admin.news.confirm_delete')), onConfirm: () => $wire.delete({{ $item->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa-regular fa-newspaper d-block"></i>
                                    <div class="fw-semibold">{{ __('admin.news.no_news_match') }}</div>
                                    <div class="small mt-1">{{ __('admin.news.try_clear_filters') }} <a href="{{ route('admin.news.create') }}">{{ __('admin.news.create_new_article') }}</a>.</div>
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
                    {{ __('admin.common.showing_range', ['from' => $newsList->firstItem(), 'to' => $newsList->lastItem(), 'total' => $newsList->total()]) }}
                </div>
                <div>{{ $newsList->onEachSide(1)->links() }}</div>
            </div>
        @endif
    </div>
</div>
