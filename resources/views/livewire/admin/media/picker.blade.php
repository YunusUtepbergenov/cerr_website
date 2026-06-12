<div>
    @if ($open)
        <div style="position: fixed; inset: 0; z-index: 1080; background: rgba(15,23,42,.55); display: flex; align-items: center; justify-content: center;"
             wire:click.self="$set('open', false)">
            <div style="background: var(--admin-surface); border-radius: 12px; padding: 1.25rem; max-width: 920px; width: calc(100% - 2rem); max-height: 80vh; overflow: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('admin.media.title_section') }}</h5>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$set('open', false)">×</button>
                </div>
                <div class="mb-3">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('admin.media.search') }}">
                </div>
                @if ($files->isEmpty())
                    <div class="empty-state"><i class="fa-regular fa-image d-block"></i><div>{{ __('admin.media.no_files') }}</div></div>
                @else
                    <div class="row g-2">
                        @foreach ($files as $f)
                            <div class="col-md-3" wire:key="picker-{{ $f['path'] }}">
                                <button type="button" wire:click="pick('{{ $f['path'] }}')" class="btn p-0 w-100" style="border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden; background: var(--admin-surface);">
                                    <img src="{{ $f['url'] }}" alt="" style="width: 100%; height: 110px; object-fit: cover;">
                                    <div class="p-2 text-truncate small">{{ $f['name'] }}</div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
