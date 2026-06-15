<div class="echo-blog-area" style="padding: 60px 0;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h2 class="m-0">@lang('messages.open_data')</h2>
            <div class="d-flex flex-wrap gap-2">
                <select wire:model.live="year" class="form-select" style="min-width: 180px;">
                    <option value="">@lang('messages.open_data_all_years')</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                <select wire:model.live="quarter" class="form-select" style="min-width: 180px;">
                    <option value="">@lang('messages.open_data_all_quarters')</option>
                    @foreach ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }} @lang('messages.open_data_quarter')</option>
                    @endforeach
                </select>
            </div>
        </div>

        @forelse ($entries as $entry)
            <div class="card mb-3 border-0 shadow-sm" wire:key="od-{{ $entry->id }}">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="flex-grow-1">
                        <h5 class="mb-2">{{ $entry->title() }}</h5>
                        <div class="text-muted small">
                            {{ $entry->year }}@if ($entry->quarter) · {{ $entry->quarterLabel() }} @lang('messages.open_data_quarter')@endif
                            · {{ $entry->fileExtension() }} · {{ $entry->fileSizeForHumans() }}
                            · <i class="fa-solid fa-download"></i> {{ $entry->download_count }}
                        </div>
                    </div>
                    <a href="{{ $entry->downloadUrl() }}" class="btn btn-primary">
                        <i class="fa-solid fa-download me-1"></i> @lang('messages.open_data_download')
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                {{ __('messages.open_data_empty') }}
            </div>
        @endforelse

        <div class="mt-4">
            {{ $entries->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
