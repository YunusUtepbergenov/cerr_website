<div>
    <div class="page-header">
        <div>
            <h1>{{ $news?->exists ? 'Edit article' : 'New article' }}</h1>
            <div class="subtitle">
                @if ($news?->exists)
                    #{{ $news->id }} · last saved {{ $news->updated_at?->diffForHumans() ?? '—' }}
                @else
                    Create a new multilingual news article
                @endif
            </div>
        </div>
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

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
                                        {{ strtoupper($locale) }}
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
                                    <label class="form-label">Title @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <input type="text" wire:model="translations.{{ $locale }}.title" class="form-control @error('translations.'.$locale.'.title') is-invalid @enderror" placeholder="Article headline in {{ strtoupper($locale) }}">
                                    @error("translations.$locale.title") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Short description @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <textarea wire:model="translations.{{ $locale }}.short_description" class="form-control @error('translations.'.$locale.'.short_description') is-invalid @enderror" rows="2" placeholder="One- or two-sentence summary"></textarea>
                                    @error("translations.$locale.short_description") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Content @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <div wire:ignore>
                                        <textarea
                                            id="editor-{{ $locale }}"
                                            class="tinymce-editor"
                                            data-locale="{{ $locale }}">{!! $translations[$locale]['content'] ?? '' !!}</textarea>
                                    </div>
                                    @error("translations.$locale.content") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <details class="mb-3">
                                    <summary class="text-muted small mb-2" style="cursor: pointer;"><i class="fa-solid fa-magnifying-glass me-1"></i> SEO ({{ strtoupper($locale) }})</summary>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">SEO title</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_title" class="form-control" placeholder="Falls back to title">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">SEO description</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_description" class="form-control" placeholder="160 characters max">
                                        </div>
                                    </div>
                                </details>

                                <div class="mt-3 pt-3" style="border-top: 1px solid var(--admin-border-soft);">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Cover image ({{ strtoupper($locale) }})</span>
                                        <span class="text-muted small fw-normal">JPG, PNG, WebP — up to 5 MB</span>
                                    </label>
                                    @php
                                        $existingImage = $translations[$locale]['image_url'] ?? null;
                                        $previewUrl = null;
                                        if ($existingImage) {
                                            $previewUrl = str_starts_with($existingImage, 'news/')
                                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage)
                                                : asset('images/news/'.$existingImage);
                                        }
                                    @endphp
                                    @if ($previewUrl)
                                        <div class="d-flex align-items-start gap-3 mb-2 p-2" style="background: #fafbfc; border: 1px solid var(--admin-border-soft); border-radius: 8px;">
                                            <img src="{{ $previewUrl }}" alt="" style="width: 140px; height: 96px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
                                            <div class="flex-grow-1">
                                                <div class="small text-muted text-truncate">{{ basename($existingImage) }}</div>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" wire:click="clearCover('{{ $locale }}')" wire:confirm="Remove the current cover image?">
                                                    <i class="fa-solid fa-trash me-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" wire:model="cover_uploads.{{ $locale }}" accept="image/*" class="form-control @error('cover_uploads.'.$locale) is-invalid @enderror">
                                    @error("cover_uploads.$locale") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="cover_uploads.{{ $locale }}" class="text-muted small mt-1">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Uploading…
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3" style="position: sticky; top: 80px;">
                    <div class="card-header">Publishing</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="my-article-url">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Used in the article URL.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="draft">Draft — only visible to admins</option>
                                <option value="published">Published — live now</option>
                                <option value="auto_publish">Auto publish — at scheduled time</option>
                                <option value="disabled">Disabled — hidden from site</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Scheduled at</label>
                            <input type="datetime-local" wire:model="scheduled_at" class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" wire:model="is_main" id="is_main" class="form-check-input">
                            <label for="is_main" class="form-check-label">Show on homepage hero</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> Save article</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> Saving…</span>
                        </button>
                        @if ($news?->exists)
                            <a href="{{ route('show.news', $news->slug) }}" target="_blank" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View on site
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Category</div>
                    <div class="card-body">
                        <select wire:model="category_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ optional($cat->translations->firstWhere('language', app()->getLocale()))->name ?? optional($cat->translations->first())->name ?? $cat->slug }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Tags</span>
                        <span class="text-muted small">{{ count($tag_ids) }} selected</span>
                    </div>
                    <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($allTags as $tag)
                            <div class="form-check">
                                <input type="checkbox" id="tag-{{ $tag->id }}" value="{{ $tag->id }}" wire:model="tag_ids" class="form-check-input">
                                <label for="tag-{{ $tag->id }}" class="form-check-label">{{ $tag->name }}</label>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <div class="small">No tags yet. <a href="{{ route('admin.tags.index') }}">Create one</a>.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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
                        plugins: 'lists link image table code paste autolink media',
                        toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image media table | alignleft aligncenter alignright | code',
                        paste_data_images: false,
                        automatic_uploads: true,
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
