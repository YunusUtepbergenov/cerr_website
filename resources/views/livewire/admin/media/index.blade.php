<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.media.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.media.subtitle') }}</div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('admin.media.search') }}">
                </div>
                <div class="col-md-6">
                    <select wire:model.live="folder" class="form-select">
                        <option value="">{{ __('admin.media.all_folders') }}</option>
                        <option value="news/covers">{{ __('admin.media.covers') }}</option>
                        <option value="news/inline">{{ __('admin.media.inline') }}</option>
                        <option value="pages">{{ __('admin.media.pages') }}</option>
                        <option value="videos">{{ __('admin.media.videos') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if ($files->isEmpty())
        <div class="card"><div class="empty-state"><i class="fa-regular fa-image d-block"></i><div class="fw-semibold">{{ __('admin.media.no_files') }}</div></div></div>
    @else
        <div class="row g-3">
            @foreach ($files as $f)
                <div class="col-md-4 col-xl-3" wire:key="media-{{ $f['path'] }}">
                    <div class="card h-100">
                        <img src="{{ $f['url'] }}" alt="" style="width: 100%; height: 160px; object-fit: cover; border-radius: 10px 10px 0 0;">
                        <div class="card-body">
                            <div class="text-truncate fw-semibold small" title="{{ $f['name'] }}">{{ $f['name'] }}</div>
                            <div class="text-muted small mt-1">
                                {{ number_format($f['size'] / 1024, 1) }} KB · {{ \Carbon\Carbon::createFromTimestamp($f['modified'])->diffForHumans() }}
                            </div>
                        </div>
                        <div class="card-footer p-2 d-flex justify-content-between align-items-center">
                            <span class="lang-chip">{{ $f['folder'] }}</span>
                            <button class="btn btn-sm btn-outline-danger" type="button" x-data
                                    @click="$dispatch('open-confirm', { message: @js(__('admin.media.confirm_delete')), onConfirm: () => $wire.delete('{{ $f['path'] }}') })">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
