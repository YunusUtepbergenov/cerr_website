<div>
    <livewire:admin.media.media-picker />

    <x-admin.page-header
        :title="$news?->exists ? __('admin.news.edit_article') : __('admin.news.new_article')"
        :subtitle="$news?->exists
            ? '#'.$news->id.' · '.__('admin.news.last_saved', ['time' => $news->updated_at?->diffForHumans() ?? '—'])
            : __('admin.news.create_article')">
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('admin.news.back_to_list') }}
        </a>
    </x-admin.page-header>

    <form wire:submit.prevent="save">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <ul class="nav nav-tabs lang-tabs">
                            @foreach (\App\Livewire\Admin\News\NewsForm::LOCALES as $locale)
                                @php
                                    $hasTitle = trim((string) ($translations[$locale]['title'] ?? '')) !== '';
                                    $hasErrors = false;
                                    foreach (['title', 'short_description', 'content'] as $field) {
                                        if ($errors->has("translations.$locale.$field")) {
                                            $hasErrors = true; break;
                                        }
                                    }
                                @endphp
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $activeLocale === $locale ? 'active' : '' }}" wire:click.prevent="setLocale('{{ $locale }}')">
                                        {{ \App\Support\Locales::label($locale) }}
                                        @if ($hasErrors)
                                            <i class="fa-solid fa-circle-exclamation text-danger lang-status"></i>
                                        @elseif ($hasTitle)
                                            <i class="fa-solid fa-circle-check text-success lang-status"></i>
                                        @else
                                            <i class="fa-regular fa-circle text-muted lang-status"></i>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        @foreach (\App\Livewire\Admin\News\NewsForm::LOCALES as $locale)
                            <div @class(['d-none' => $activeLocale !== $locale])>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.news.title') }} @if ($locale === \App\Livewire\Admin\News\NewsForm::PRIMARY_LOCALE) <span class="text-danger">*</span> @endif</label>
                                    <input type="text" wire:model="translations.{{ $locale }}.title" class="form-control @error('translations.'.$locale.'.title') is-invalid @enderror" placeholder="{{ __('admin.news.title_placeholder', ['locale' => \App\Support\Locales::label($locale)]) }}">
                                    @error("translations.$locale.title") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.news.short_description') }} @if ($locale === \App\Livewire\Admin\News\NewsForm::PRIMARY_LOCALE) <span class="text-danger">*</span> @endif</label>
                                    <textarea wire:model="translations.{{ $locale }}.short_description" class="form-control @error('translations.'.$locale.'.short_description') is-invalid @enderror" rows="2" placeholder="{{ __('admin.news.short_description_placeholder') }}"></textarea>
                                    @error("translations.$locale.short_description") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.news.content') }} @if ($locale === \App\Livewire\Admin\News\NewsForm::PRIMARY_LOCALE) <span class="text-danger">*</span> @endif</label>
                                    <div wire:ignore>
                                        <textarea
                                            id="editor-{{ $locale }}"
                                            class="tinymce-editor"
                                            data-locale="{{ $locale }}">{!! $translations[$locale]['content'] ?? '' !!}</textarea>
                                    </div>
                                    @error("translations.$locale.content") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <details class="mb-3">
                                    <summary class="text-muted small mb-2" style="cursor: pointer;"><i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('admin.news.seo', ['locale' => \App\Support\Locales::label($locale)]) }}</summary>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_title') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_title" class="form-control" placeholder="{{ __('admin.news.seo_title_placeholder') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_description') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_description" class="form-control" placeholder="{{ __('admin.news.seo_description_placeholder') }}">
                                        </div>
                                    </div>
                                </details>

                                <div class="mt-3 pt-3" style="border-top: 1px solid var(--admin-border-soft);"
                                     x-data
                                     x-on:media-picked.window="(e) => { if ($wire.activeLocale === '{{ $locale }}') { $wire.set('translations.{{ $locale }}.image_url', e.detail.path); } }">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>{{ __('admin.news.cover_image', ['locale' => \App\Support\Locales::label($locale)]) }}</span>
                                        <span class="text-muted small fw-normal">{{ __('admin.news.cover_specs') }}</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2"
                                            @click="$dispatch('show-picker', { folder: 'news/covers' })">
                                        <i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}
                                    </button>
                                    @php
                                        $existingImage = $translations[$locale]['image_url'] ?? null;
                                        $stagedUpload = $cover_uploads[$locale] ?? null;
                                        $previewUrl = null;
                                        if ($stagedUpload) {
                                            try { $previewUrl = $stagedUpload->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; }
                                        }
                                        if (! $previewUrl && $existingImage) {
                                            $previewUrl = str_starts_with($existingImage, 'news/')
                                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage)
                                                : asset('images/news/'.$existingImage);
                                        }
                                    @endphp
                                    @if ($previewUrl)
                                        <div class="d-flex align-items-start gap-3 mb-2 p-2" style="background: var(--admin-surface-soft); border: 1px solid var(--admin-border-soft); border-radius: 8px;">
                                            <img src="{{ $previewUrl }}" alt="" style="width: 140px; height: 96px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
                                            <div class="flex-grow-1">
                                                <div class="small text-muted text-truncate">{{ basename($existingImage) }}</div>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                    x-data
                                                    @click="$dispatch('open-confirm', { message: @js(__('admin.news.confirm_remove_cover')), onConfirm: () => $wire.clearCover('{{ $locale }}') })">
                                                    <i class="fa-solid fa-trash me-1"></i> {{ __('admin.common.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    <div x-data="{ dragging: false }"
                                         class="position-relative"
                                         :class="{ 'is-dragging': dragging }"
                                         @dragover.prevent="dragging = true"
                                         @dragleave.prevent="dragging = false"
                                         @drop.prevent="
                                            dragging = false;
                                            if ($event.dataTransfer.files.length) {
                                                const input = $el.querySelector('input[type=file]');
                                                input.files = $event.dataTransfer.files;
                                                input.dispatchEvent(new Event('change'));
                                            }
                                         "
                                         style="border: 2px dashed transparent; border-radius: 8px; padding: 4px; transition: all .15s;">
                                        <div x-show="dragging" x-cloak style="position: absolute; inset: 0; background: rgba(37,99,235,.08); border: 2px dashed #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; pointer-events: none; font-weight: 600; color: #2563eb;">
                                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> {{ __('admin.news.drop_here') }}
                                        </div>
                                        <input type="file" wire:model="cover_uploads.{{ $locale }}" accept="image/*"
                                               class="form-control @error('cover_uploads.'.$locale) is-invalid @enderror">
                                    </div>
                                    @error("cover_uploads.$locale") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="cover_uploads.{{ $locale }}" class="text-muted small mt-1">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.uploading') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-form-sidebar">
                <div class="card mb-3">
                    <div class="card-header">{{ __('admin.news.publishing') }}</div>
                    <div class="card-body">
                        <div class="mb-3"
                             x-data="{ manuallyEdited: @js((bool) ($news?->exists)), debounce: null, slugCheck: null }"
                             x-init="
                                $watch(() => $wire.translations.uz.title, (title) => {
                                    if (manuallyEdited) return;
                                    if (typeof title !== 'string') return;
                                    clearTimeout(debounce);
                                    debounce = setTimeout(() => $wire.regenerateSlug(title), 250);
                                });
                             ">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug"
                                   @input="manuallyEdited = true; clearTimeout(debounce); clearTimeout(slugCheck); slugCheck = setTimeout(() => $wire.checkSlugAvailability(), 400)"
                                   @blur="$wire.normalizeSlug()"
                                   class="form-control @error('slug') is-invalid @enderror" placeholder="my-article-url">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.news.slug_help') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.common.status') }}</label>
                            <select wire:model="status" class="form-select">
                                <option value="draft">{{ __('admin.news.status_draft') }}</option>
                                <option value="published">{{ __('admin.news.status_published') }}</option>
                                <option value="auto_publish">{{ __('admin.news.status_auto_publish') }}</option>
                                <option value="disabled">{{ __('admin.news.status_disabled') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.news.scheduled_at') }}</label>
                            <input type="datetime-local" wire:model="scheduled_at" class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" wire:model="is_main" id="is_main" class="form-check-input">
                            <label for="is_main" class="form-check-label">{{ __('admin.news.show_on_homepage') }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.news.save_article') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                        @if ($news?->exists)
                            <a href="{{ route('show.news', $news->slug) }}" target="_blank" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('admin.news.view_on_site') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">{{ __('admin.news.category') }}</div>
                    <div class="card-body">
                        <select wire:model="category_id" class="form-select">
                            <option value="">{{ __('admin.common.none') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ optional($cat->translations->firstWhere('language', app()->getLocale()))->name ?? optional($cat->translations->first())->name ?? $cat->slug }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('admin.news.tags') }}</span>
                        <span class="text-muted small">{{ __('admin.news.tags_selected', ['count' => count($tag_ids)]) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="tag-chip-list">
                        @forelse ($allTags as $tag)
                            <div class="form-check">
                                <input type="checkbox" id="tag-{{ $tag->id }}" value="{{ $tag->id }}" wire:model="tag_ids" class="form-check-input">
                                <label for="tag-{{ $tag->id }}" class="form-check-label">{{ $tag->name }}</label>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <div class="small">{{ __('admin.news.no_tags_yet') }} <a href="{{ route('admin.tags.index') }}">{{ __('admin.news.create_one') }}</a>.</div>
                            </div>
                        @endforelse
                        </div>
                    </div>
                </div>

                @if ($news?->exists)
                    <div class="card mt-3">
                        <div class="card-header">{{ __('admin.activity.title_section') }}</div>
                        <div class="card-body">
                            @forelse ($news->activity()->with('user')->latest()->limit(10)->get() as $a)
                                <div class="d-flex gap-2 align-items-start mb-2 pb-2 border-bottom">
                                    <i class="fa-solid fa-clock-rotate-left text-muted small mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <strong>{{ $a->user->name ?? __('admin.activity.system') }}</strong>
                                            {{ __('admin.activity.action_'.$a->action) }}
                                        </div>
                                        <div class="text-muted small">{{ $a->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">{{ __('admin.activity.no_activity') }}</div>
                            @endforelse
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>

        <div class="form-action-bar d-lg-none">
            <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">{{ __('admin.news.back_to_list') }}</a>
            <button type="submit" class="btn btn-primary">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.news.save_article') }}</span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (function () {
                const initEditor = (textarea) => {
                    if (!window.tinymce) { return; }
                    const locale = textarea.dataset.locale;
                    if (textarea.dataset.tmceInitialized === '1') { return; }
                    textarea.dataset.tmceInitialized = '1';

                    window.tinymce.init({
                        target: textarea,
                        height: 480,
                        menubar: false,
                        promotion: false,
                        branding: false,
                        content_css: '{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}',
                        body_class: 'news-article-body',
                        plugins: 'lists link image table code autolink media',
                        toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image media table | alignleft aligncenter alignright | code',
                        paste_data_images: false,
                        automatic_uploads: true,
                        file_picker_types: 'image',
                        file_picker_callback: function (callback, value, meta) {
                            window.dispatchEvent(new CustomEvent('show-picker', { detail: { folder: 'news/inline' } }));
                            window.addEventListener('media-picked', function handler(e) {
                                callback(e.detail.url, { alt: '' });
                                window.removeEventListener('media-picked', handler);
                            }, { once: true });
                        },
                        images_upload_url: '{{ route('admin.inline-image.store') }}',
                        images_upload_handler: function (blobInfo, progress) {
                            return new Promise((resolve, reject) => {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route('admin.inline-image.store') }}');
                                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                                xhr.setRequestHeader('Accept', 'application/json');
                                xhr.upload.onprogress = (e) => { if (e.lengthComputable) progress(e.loaded / e.total * 100); };
                                xhr.onload = () => {
                                    if (xhr.status < 200 || xhr.status >= 300) {
                                        reject({ message: 'Upload failed: ' + xhr.status, remove: true });
                                        return;
                                    }
                                    try {
                                        const json = JSON.parse(xhr.responseText);
                                        resolve(json.location);
                                    } catch (e) {
                                        reject({ message: 'Invalid upload response', remove: true });
                                    }
                                };
                                xhr.onerror = () => reject({ message: 'Network error during upload', remove: true });
                                const fd = new FormData();
                                fd.append('file', blobInfo.blob(), blobInfo.filename());
                                xhr.send(fd);
                            });
                        },
                        setup: (editor) => {
                            editor.on('change input keyup', () => {
                                const data = editor.getContent();
                                window.Livewire.find('{{ $this->getId() }}').set('translations.' + locale + '.content', data, false);
                            });
                        },
                    });
                };

                const initAll = () => {
                    document.querySelectorAll('textarea.tinymce-editor').forEach(initEditor);
                };

                if (document.readyState !== 'loading') { initAll(); }
                else { document.addEventListener('DOMContentLoaded', initAll); }

                document.addEventListener('livewire:navigated', initAll);
            })();
        </script>
    @endpush
</div>
