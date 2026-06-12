<div>
    <x-admin.page-header :title="__('admin.media.title_section')" :subtitle="__('admin.media.subtitle')" />

    @if (session('status'))
        <div class="alert alert-success alert-dismissible mb-3">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <link rel="stylesheet" href="https://unpkg.com/filepond@4/dist/filepond.min.css">
    <link rel="stylesheet" href="https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.min.css">
    <style>
        .filepond--root { font-family: inherit; }
        .filepond--panel-root { background: var(--admin-surface-soft); border: 1px dashed var(--admin-border); }
        .filepond--drop-label { color: var(--admin-text-muted); font-size: .95rem; }
        .filepond--label-action { color: var(--admin-primary); text-decoration: underline; }
    </style>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('admin.media.upload') }}</span>
            <div style="min-width: 220px;">
                <select id="filepond-folder" class="form-select form-select-sm">
                    <option value="news/covers">{{ __('admin.media.covers') }}</option>
                    <option value="news/inline">{{ __('admin.media.inline') }}</option>
                    <option value="pages">{{ __('admin.media.pages') }}</option>
                    <option value="videos">{{ __('admin.media.videos') }}</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <input type="file" id="filepond-input" multiple>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/filepond@4/dist/filepond.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type@1/dist/filepond-plugin-file-validate-type.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-size@2/dist/filepond-plugin-file-validate-size.min.js"></script>
        <script>
            (function () {
                if (window.FilePond) {
                    FilePond.registerPlugin(
                        FilePondPluginImagePreview,
                        FilePondPluginFileValidateType,
                        FilePondPluginFileValidateSize,
                    );
                }

                const init = () => {
                    const input = document.getElementById('filepond-input');
                    if (! input || input.dataset.filepondInitialized === '1') return;
                    if (! window.FilePond) return;
                    input.dataset.filepondInitialized = '1';

                    const folderSelect = document.getElementById('filepond-folder');
                    const csrf = '{{ csrf_token() }}';

                    FilePond.create(input, {
                        allowMultiple: true,
                        allowReorder: true,
                        acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        maxFileSize: '5MB',
                        labelIdle: @js(__('admin.media.filepond_idle')),
                        labelFileLoading: '{{ __('admin.media.filepond_loading') }}',
                        labelFileProcessing: '{{ __('admin.media.filepond_uploading') }}',
                        labelFileProcessingComplete: '{{ __('admin.media.filepond_done') }}',
                        labelFileProcessingError: '{{ __('admin.media.filepond_error') }}',
                        labelFileTypeNotAllowed: '{{ __('admin.media.filepond_bad_type') }}',
                        fileValidateTypeLabelExpectedTypes: 'JPG, PNG, WebP, GIF',
                        labelMaxFileSize: '{{ __('admin.media.filepond_max_size') }}',
                        server: {
                            process: (fieldName, file, metadata, load, error, progress, abort) => {
                                const formData = new FormData();
                                formData.append('file', file, file.name);
                                formData.append('folder', folderSelect.value);

                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route('admin.media.upload') }}');
                                xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
                                xhr.setRequestHeader('Accept', 'application/json');

                                xhr.upload.onprogress = (e) => {
                                    if (e.lengthComputable) progress(true, e.loaded, e.total);
                                };

                                xhr.onload = () => {
                                    if (xhr.status >= 200 && xhr.status < 300) {
                                        let body;
                                        try { body = JSON.parse(xhr.responseText); } catch (e) { body = {}; }
                                        load(body.path || file.name);
                                        if (window.Livewire) {
                                            const cmp = Livewire.find('{{ $this->getId() }}');
                                            if (cmp) cmp.call('$refresh');
                                        }
                                    } else {
                                        let msg = '{{ __('admin.media.filepond_error') }}';
                                        try {
                                            const body = JSON.parse(xhr.responseText);
                                            if (body && body.message) msg = body.message;
                                        } catch (e) {}
                                        error(msg);
                                    }
                                };

                                xhr.onerror = () => error('{{ __('admin.media.filepond_error') }}');
                                xhr.send(formData);

                                return { abort: () => { xhr.abort(); abort(); } };
                            },
                            revert: null,
                        },
                    });
                };

                if (document.readyState !== 'loading') init();
                else document.addEventListener('DOMContentLoaded', init);
                document.addEventListener('livewire:navigated', init);
            })();
        </script>
    @endpush

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
        <div class="card"><x-admin.empty-state icon="fa-regular fa-image" :title="__('admin.media.no_files')" /></div>
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
