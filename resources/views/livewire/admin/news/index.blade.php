<div>
    <x-admin.page-header :title="__('admin.news.title_section')" :subtitle="__('admin.news.subtitle')">
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}
        </a>
    </x-admin.page-header>

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

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
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
                                ?? null;
                            $thumbUrl = $translation?->coverUrl();
                            $availableLocales = $item->translations->pluck('lang')->all();
                        @endphp
                        <tr wire:key="news-{{ $item->id }}" data-href="{{ route('admin.news.edit', $item) }}">
                            <td>
                                @if ($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border-soft);">
                                @else
                                    <div class="thumb-placeholder"><i class="fa-regular fa-image"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $translation->title ?? '—' }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 360px;">{{ $item->slug }}</div>
                            </td>
                            <td><x-admin.status-pill :status="$item->status" /></td>
                            <td><x-admin.lang-chips :available="$availableLocales" /></td>
                            <td>
                                @if ($catName)
                                    <span class="cat-badge" title="{{ $catName }}">{{ $catName }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="text-muted small"><i class="fa-regular fa-eye me-1"></i>{{ number_format($item->view_count) }}</span></td>
                            <td><span class="text-muted small">{{ ($item->updated_at ?? $item->created_at)?->diffForHumans() ?? '—' }}</span></td>
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
                            <td colspan="8">
                                <x-admin.empty-state icon="fa-regular fa-newspaper" :title="__('admin.news.no_news_match')">
                                    {{ __('admin.news.try_clear_filters') }} <a href="{{ route('admin.news.create') }}">{{ __('admin.news.create_new_article') }}</a>.
                                </x-admin.empty-state>
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
