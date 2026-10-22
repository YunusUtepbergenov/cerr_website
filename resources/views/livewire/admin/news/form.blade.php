@php
    $locales = \App\Livewire\Admin\News\NewsForm::LOCALES;
    $filledLocales = collect($locales)->filter(fn ($l) => trim((string) ($translations[$l]['title'] ?? '')) !== '')->count();
    $siteHost = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();
    $statusKeys = ['draft', 'published', 'auto_publish', 'disabled'];
    $statusIcons = ['draft' => 'fa-pen-ruler', 'published' => 'fa-circle-check', 'auto_publish' => 'fa-clock', 'disabled' => 'fa-eye-slash'];
    $isEdit = (bool) $news?->exists;
@endphp

<div class="news-editor"
     x-data="{
         statusLabels: @js(collect($statusKeys)->mapWithKeys(fn ($k) => [$k => __('admin.news.status_short_'.$k)])),
         statusHelp: @js(collect($statusKeys)->mapWithKeys(fn ($k) => [$k => __('admin.news.status_'.$k)])),
     }"
     x-on:keydown.window.ctrl.s.prevent="$refs.saveButton.click()"
     x-on:keydown.window.meta.s.prevent="$refs.saveButton.click()">
    <livewire:admin.media.media-picker />

    <form wire:submit.prevent="save" id="news-form">

        {{-- Sticky command bar: title, live status, save --}}
        <header class="editor-bar">
            <a href="{{ route('admin.news.index') }}" class="editor-bar-back" title="{{ __('admin.news.back_to_list') }}" aria-label="{{ __('admin.news.back_to_list') }}">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="editor-bar-title">
                <h1>{{ $isEdit ? __('admin.news.edit_article') : __('admin.news.new_article') }}</h1>
                <div class="editor-bar-meta">
                    @if ($isEdit)
                        <span>#{{ $news->id }}</span>
                        <span class="dot"></span>
                        <span>{{ __('admin.news.last_saved', ['time' => $news->updated_at?->diffForHumans() ?? '—']) }}</span>
                    @else
                        <span>{{ __('admin.news.create_article') }}</span>
                    @endif
                </div>
            </div>
            <div class="editor-bar-actions">
                <span class="pill d-none d-sm-inline-flex" x-bind:class="'status-' + $wire.status" x-text="statusLabels[$wire.status]">{{ __('admin.news.status_short_'.$status) }}</span>
                @if ($isEdit)
                    <a href="{{ route('show.news', $news->slug) }}" target="_blank" class="btn btn-outline-secondary d-none d-md-inline-flex align-items-center">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('admin.news.view_on_site') }}
                    </a>
                @endif
                <button type="submit" class="btn btn-primary editor-save" x-ref="saveButton">
                    <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.news.save_article') }}</span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                </button>
            </div>
        </header>

        <div class="editor-layout">

            {{-- ============ Document ============ --}}
            <section class="editor-main">
                @error('translations')
                    <div class="alert alert-danger py-2 px-3 mb-3">{{ $message }}</div>
                @enderror

                {{-- Language tabs sit on the top edge of the paper --}}
                <div class="doc-tabs-row">
                    <div class="doc-tabs" role="tablist">
                        @foreach ($locales as $locale)
                            @php
                                $hasTitle = trim((string) ($translations[$locale]['title'] ?? '')) !== '';
                                $hasErrors = collect(['title', 'short_description', 'content'])->contains(fn ($f) => $errors->has("translations.$locale.$f"));
                            @endphp
                            <button type="button" role="tab" aria-selected="{{ $activeLocale === $locale ? 'true' : 'false' }}"
                                    class="doc-tab {{ $activeLocale === $locale ? 'active' : '' }}"
                                    wire:click.prevent="setLocale('{{ $locale }}')">
                                <span class="doc-tab-code">{{ \App\Support\Locales::label($locale) }}</span>
                                <span class="doc-tab-name">{{ \App\Support\Locales::nativeName($locale) }}</span>
                                <span class="doc-tab-dot {{ $hasErrors ? 'error' : ($hasTitle ? 'done' : '') }}"></span>
                            </button>
                        @endforeach
                    </div>
                    <div class="doc-tabs-note" title="{{ __('admin.news.translation_hint') }}">
                        <i class="fa-regular fa-circle-question"></i>
                        <span>{{ __('admin.news.translation_progress', ['done' => $filledLocales, 'total' => count($locales)]) }}</span>
                    </div>
                </div>

                @foreach ($locales as $locale)
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
                    <article @class(['doc-paper', 'd-none' => $activeLocale !== $locale])
                             x-data="{ dragging: false }"
                             x-on:media-picked.window="(e) => { if ($wire.activeLocale === '{{ $locale }}') { $wire.set('translations.{{ $locale }}.image_url', e.detail.path); } }">
                        <input type="file" id="cover-file-{{ $locale }}" wire:model="cover_uploads.{{ $locale }}" accept="image/*"
                               class="visually-hidden @error('cover_uploads.'.$locale) is-invalid @enderror">

                        {{-- Cover: the first thing on the page, like the article itself --}}
                        @if ($previewUrl)
                            <div class="doc-cover has-image" :class="{ 'is-dragging': dragging }"
                                 x-on:dragover.prevent="dragging = true"
                                 x-on:dragleave.prevent="dragging = false"
                                 x-on:drop.prevent="
                                    dragging = false;
                                    if ($event.dataTransfer.files.length) {
                                        const input = document.getElementById('cover-file-{{ $locale }}');
                                        input.files = $event.dataTransfer.files;
                                        input.dispatchEvent(new Event('change'));
                                    }
                                 ">
                                <img src="{{ $previewUrl }}" alt="">
                                <div class="doc-cover-actions">
                                    <label for="cover-file-{{ $locale }}" class="btn btn-sm btn-glass mb-0">
                                        <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> {{ __('admin.news.cover_replace') }}
                                    </label>
                                    <button type="button" class="btn btn-sm btn-glass"
                                            @click="$dispatch('show-picker', { folder: 'news/covers' })">
                                        <i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-glass btn-glass-danger" title="{{ __('admin.news.remove_cover') }}" aria-label="{{ __('admin.news.remove_cover') }}"
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.news.confirm_remove_cover')), onConfirm: () => $wire.clearCover('{{ $locale }}') })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                <div class="doc-cover-overlay" x-show="dragging" x-cloak>
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> {{ __('admin.news.drop_here') }}
                                </div>
                            </div>
                        @else
                            <div class="doc-cover is-empty" :class="{ 'is-dragging': dragging }"
                                 x-on:dragover.prevent="dragging = true"
                                 x-on:dragleave.prevent="dragging = false"
                                 x-on:drop.prevent="
                                    dragging = false;
                                    if ($event.dataTransfer.files.length) {
                                        const input = document.getElementById('cover-file-{{ $locale }}');
                                        input.files = $event.dataTransfer.files;
                                        input.dispatchEvent(new Event('change'));
                                    }
                                 ">
                                <span class="doc-cover-icon"><i class="fa-regular fa-image"></i></span>
                                <span class="doc-cover-text">
                                    <b>{{ __('admin.news.cover_add') }}</b>
                                    <small>{{ __('admin.news.cover_add_hint') }} · {{ __('admin.news.cover_specs') }}</small>
                                </span>
                                <span class="doc-cover-buttons">
                                    <label for="cover-file-{{ $locale }}" class="btn btn-sm btn-outline-secondary mb-0">
                                        <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> {{ __('admin.news.cover_upload') }}
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            @click="$dispatch('show-picker', { folder: 'news/covers' })">
                                        <i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}
                                    </button>
                                </span>
                                <div class="doc-cover-overlay" x-show="dragging" x-cloak>
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> {{ __('admin.news.drop_here') }}
                                </div>
                            </div>
                        @endif
                        @error("cover_uploads.$locale") <div class="text-danger small doc-inset mt-2">{{ $message }}</div> @enderror
                        <div wire:loading wire:target="cover_uploads.{{ $locale }}" class="text-muted small doc-inset mt-2">
                            <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.uploading') }}
                        </div>

                        {{-- Headline + lead, typed straight onto the page --}}
                        <div class="doc-head">
                            <label class="visually-hidden" for="title-{{ $locale }}">{{ __('admin.news.title') }}</label>
                            <textarea id="title-{{ $locale }}" wire:model="translations.{{ $locale }}.title" rows="1"
                                      class="doc-title @error('translations.'.$locale.'.title') is-invalid @enderror"
                                      placeholder="{{ __('admin.news.title_placeholder', ['locale' => \App\Support\Locales::label($locale)]) }}"
                                      x-data x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                      x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                      x-on:keydown.enter.prevent></textarea>
                            @error("translations.$locale.title") <div class="doc-error">{{ $message }}</div> @enderror

                            <label class="visually-hidden" for="lead-{{ $locale }}">{{ __('admin.news.short_description') }}</label>
                            <textarea id="lead-{{ $locale }}" wire:model="translations.{{ $locale }}.short_description" rows="2"
                                      class="doc-lead @error('translations.'.$locale.'.short_description') is-invalid @enderror"
                                      placeholder="{{ __('admin.news.short_description_placeholder') }}"
                                      x-data x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                      x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"></textarea>
                            @error("translations.$locale.short_description") <div class="doc-error">{{ $message }}</div> @enderror
                        </div>

                        {{-- Body --}}
                        <div class="doc-editor" wire:ignore>
                            <textarea id="editor-{{ $locale }}" class="tinymce-editor" data-locale="{{ $locale }}">{!! $translations[$locale]['content'] ?? '' !!}</textarea>
                        </div>
                        @error("translations.$locale.content") <div class="doc-error doc-inset">{{ $message }}</div> @enderror

                        {{-- Search appearance --}}
                        <details class="doc-seo">
                            <summary>
                                <span class="doc-seo-icon"><i class="fa-brands fa-google"></i></span>
                                <span class="doc-seo-text">
                                    <b>{{ __('admin.news.seo_section') }}</b>
                                    <small>{{ __('admin.news.seo_fallback_note') }}</small>
                                </span>
                                <i class="fa-solid fa-chevron-down doc-seo-chevron"></i>
                            </summary>
                            <div class="doc-seo-body">
                                <div class="serp-preview">
                                    <div class="serp-site">
                                        <span class="serp-favicon">C</span>
                                        <span>
                                            <span class="serp-host">{{ $siteHost }}</span>
                                            <span class="serp-url">{{ $siteHost }} › show-news › <span x-text="$wire.slug || 'my-article-url'"></span></span>
                                        </span>
                                    </div>
                                    <div class="serp-title" x-text="$wire.translations['{{ $locale }}']?.seo_title || $wire.translations['{{ $locale }}']?.title || @js(__('admin.news.title_placeholder', ['locale' => \App\Support\Locales::label($locale)]))"></div>
                                    <div class="serp-desc" x-text="$wire.translations['{{ $locale }}']?.seo_description || $wire.translations['{{ $locale }}']?.short_description || @js(__('admin.news.short_description_placeholder'))"></div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="field-label d-flex justify-content-between" for="seo-title-{{ $locale }}">
                                            <span>{{ __('admin.news.seo_title') }}</span>
                                            <span class="field-counter"
                                                  :class="{ 'over': ($wire.translations['{{ $locale }}']?.seo_title ?? '').length > 60 }"
                                                  x-text="($wire.translations['{{ $locale }}']?.seo_title ?? '').length + ' / 60'"></span>
                                        </label>
                                        <input type="text" id="seo-title-{{ $locale }}" wire:model="translations.{{ $locale }}.seo_title" class="form-control" placeholder="{{ __('admin.news.seo_title_placeholder') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="field-label d-flex justify-content-between" for="seo-desc-{{ $locale }}">
                                            <span>{{ __('admin.news.seo_description') }}</span>
                                            <span class="field-counter"
                                                  :class="{ 'over': ($wire.translations['{{ $locale }}']?.seo_description ?? '').length > 160 }"
                                                  x-text="($wire.translations['{{ $locale }}']?.seo_description ?? '').length + ' / 160'"></span>
                                        </label>
                                        <input type="text" id="seo-desc-{{ $locale }}" wire:model="translations.{{ $locale }}.seo_description" class="form-control" placeholder="{{ __('admin.news.seo_description_placeholder') }}">
                                    </div>
                                </div>
                            </div>
                        </details>
                    </article>
                @endforeach
            </section>

            {{-- ============ Inspector ============ --}}
            <aside class="editor-inspector">

                <section class="insp-section">
                    <h3 class="insp-title">{{ __('admin.news.publishing') }}</h3>
                    <div class="status-list" role="radiogroup" aria-label="{{ __('admin.common.status') }}">
                        @foreach ($statusKeys as $key)
                            <input type="radio" id="status-{{ $key }}" value="{{ $key }}" wire:model="status" name="status">
                            <label for="status-{{ $key }}" class="status-{{ $key }}">
                                <span class="status-icon"><i class="fa-solid {{ $statusIcons[$key] }}"></i></span>
                                <span class="status-text">
                                    <b>{{ __('admin.news.status_short_'.$key) }}</b>
                                    <small>{{ \Illuminate\Support\Str::of(__('admin.news.status_'.$key))->after('—')->trim() }}</small>
                                </span>
                                <span class="status-check"><i class="fa-solid fa-check"></i></span>
                            </label>
                        @endforeach
                    </div>
                    @error('status') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                    <div class="insp-field" x-show="$wire.status === 'auto_publish'" x-cloak>
                        <label class="field-label" for="scheduled_at">{{ __('admin.news.scheduled_at') }}</label>
                        <input type="datetime-local" id="scheduled_at" wire:model="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror">
                        @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('admin.news.schedule_hint') }}</div>
                    </div>
                </section>

                <section class="insp-section"
                         x-data="{ manuallyEdited: @js($isEdit), debounce: null, slugCheck: null }"
                         x-init="
                            $watch(() => $wire.translations[$wire.activeLocale]?.title, (title) => {
                                if (manuallyEdited) return;
                                // Skip blanks so switching to an unfilled tab never wipes the slug.
                                if (typeof title !== 'string' || title === '') return;
                                clearTimeout(debounce);
                                debounce = setTimeout(() => $wire.regenerateSlug(title), 250);
                            });
                         ">
                    <h3 class="insp-title">{{ __('admin.news.address') }}</h3>
                    <label class="slug-field @error('slug') is-invalid @enderror" for="slug">
                        <span class="slug-base">{{ $siteHost }}/show-news/</span>
                        <input type="text" id="slug" wire:model="slug" class="slug-input" placeholder="my-article-url" autocomplete="off" spellcheck="false"
                               @input="manuallyEdited = true; clearTimeout(debounce); clearTimeout(slugCheck); slugCheck = setTimeout(() => $wire.checkSlugAvailability(), 400)"
                               @blur="$wire.normalizeSlug()">
                    </label>
                    @error('slug') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </section>

                <section class="insp-section">
                    <h3 class="insp-title">{{ __('admin.news.category') }}</h3>
                    <label class="visually-hidden" for="category_id">{{ __('admin.news.category') }}</label>
                    <select wire:model="category_id" id="category_id" class="form-select">
                        <option value="">{{ __('admin.common.none') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ optional($cat->translations->firstWhere('language', app()->getLocale()))->name ?? optional($cat->translations->first())->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                </section>

                <section class="insp-section tag-picker"
                         x-data="{
                             query: '',
                             selected: {{ count($tag_ids) }},
                             countLabel: @js(__('admin.news.tags_selected', ['count' => '__COUNT__'])),
                             createLabel: @js(__('admin.news.tags_create', ['name' => '__NAME__'])),
                             createLabelEmpty: @js(__('admin.news.tags_create_empty')),
                             get canCreate() {
                                 return this.query.trim() !== '' && ! this.exactMatch;
                             },
                             matches(name, input) {
                                 return this.query.trim() === '' || input.checked || name.includes(this.query.trim().toLowerCase());
                             },
                             recount() {
                                 this.selected = this.$root.querySelectorAll('.tag-chip-list input:checked').length;
                             },
                             get exactMatch() {
                                 const wanted = this.query.trim().toLowerCase();
                                 return [...this.$root.querySelectorAll('.tag-chip-list .form-check')].some((chip) => chip.dataset.name === wanted);
                             },
                             createTag() {
                                 const name = this.query.trim();
                                 if (! this.canCreate) { return; }
                                 this.$wire.createTag(name).then(() => this.$nextTick(() => { this.query = ''; this.recount(); }));
                             },
                             get visibleCount() {
                                 return [...this.$root.querySelectorAll('.tag-chip-list .form-check')]
                                     .filter((chip) => this.matches(chip.dataset.name, chip.querySelector('input'))).length;
                             },
                         }">
                    <h3 class="insp-title">
                        {{ __('admin.news.tags') }}
                        <span class="insp-meta" x-text="countLabel.replace('__COUNT__', selected)">{{ __('admin.news.tags_selected', ['count' => count($tag_ids)]) }}</span>
                    </h3>
                    <div class="tag-picker-search" :class="{ 'can-create': canCreate }">
                        <i class="fa-solid fa-magnifying-glass tag-picker-glass"></i>
                        <input type="search" x-model="query" class="tag-picker-input"
                               x-on:keydown.enter.prevent="if (canCreate) { createTag() }"
                               placeholder="{{ __('admin.news.tags_search_placeholder') }}"
                               aria-label="{{ __('admin.news.tags_search_placeholder') }}">
                        <button type="button" class="tag-picker-create" x-on:click="createTag()"
                                x-bind:disabled="! canCreate" wire:loading.attr="disabled" wire:target="createTag"
                                x-bind:title="canCreate ? createLabel.replace('__NAME__', query.trim()) : createLabelEmpty">
                            <i class="fa-solid fa-plus"></i>
                            <span class="tag-picker-create-text" x-text="canCreate ? createLabel.replace('__NAME__', query.trim()) : createLabelEmpty">{{ __('admin.news.tags_create_empty') }}</span>
                        </button>
                    </div>
                    <div class="tag-chip-list tag-chip-scroll" x-on:change="recount()">
                    @forelse ($allTags as $tag)
                        <div class="form-check" wire:key="tag-chip-{{ $tag->id }}" data-name="{{ mb_strtolower($tag->name) }}"
                             x-show="matches($el.dataset.name, $el.querySelector('input'))">
                            <input type="checkbox" id="tag-{{ $tag->id }}" value="{{ $tag->id }}" wire:model="tag_ids" class="form-check-input">
                            <label for="tag-{{ $tag->id }}" class="form-check-label">{{ $tag->name }}</label>
                        </div>
                    @empty
                        <div class="text-muted small py-2">{{ __('admin.news.no_tags_yet') }} <a href="{{ route('admin.tags.index') }}">{{ __('admin.news.create_one') }}</a>.</div>
                    @endforelse
                    </div>
                    <div class="small text-muted mt-2" x-show="query.trim() !== '' && visibleCount === 0" x-cloak>{{ __('admin.news.tags_no_match') }}</div>
                    @error('new_tag') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </section>

                @if ($isEdit)
                    <section class="insp-section">
                        <h3 class="insp-title">{{ __('admin.activity.title_section') }}</h3>
                        <div class="timeline">
                        @forelse ($news->activity()->with('user')->latest()->limit(10)->get() as $a)
                            <div class="timeline-item">
                                <div class="small">
                                    <strong>{{ $a->user->name ?? __('admin.activity.system') }}</strong>
                                    {{ __('admin.activity.action_'.$a->action) }}
                                </div>
                                <div class="text-muted small">{{ $a->created_at->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="text-muted small">{{ __('admin.activity.no_activity') }}</div>
                        @endforelse
                        </div>
                    </section>
                @endif
            </aside>
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

                    const isDark = document.documentElement.getAttribute('data-admin-theme') === 'dark';
                    const bar = document.querySelector('.editor-bar');
                    const width = window.innerWidth;
                    const paperPadding = width >= 1200 ? 56 : (width >= 992 ? 36 : (width >= 576 ? 28 : 18));
                    window.tinymce.init({
                        target: textarea,
                        menubar: false,
                        toolbar_mode: 'wrap',
                        toolbar_sticky: true,
                        toolbar_sticky_offset: bar ? Math.ceil(bar.getBoundingClientRect().height) + 20 : 0,
                        statusbar: false,
                        skin: isDark ? 'oxide-dark' : 'oxide',
                        // The body's side padding matches the paper's --doc-pad so the text
                        // lines up with the headline; the iframe is sized to the body below.
                        content_style: 'html { overflow-y: hidden; } body.news-article-body { margin: 0; padding: 1.4rem ' + paperPadding + 'px 2rem; max-width: none;'
                            + " font-family: 'Source Sans 3', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }"
                            + (isDark
                                ? ' body.news-article-body { background: #191c30; color: #eef1fb; }'
                                    + ' body.news-article-body :is(p, li, td, th, blockquote, figcaption) { color: #d9ddf0; }'
                                    + ' body.news-article-body :is(h1, h2, h3, h4, h5, h6, strong, b, em, i) { color: #f3f5ff; }'
                                    + ' body.news-article-body a { color: #8b94ec; }'
                                : ''),
                        promotion: false,
                        branding: false,
                        content_css: '{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}',
                        body_class: 'news-article-body',
                        plugins: 'lists link image table code autolink media',
                        toolbar: [
                            'undo redo | styles | bold italic underline | bullist numlist | link',
                            'image media table | alignleft aligncenter alignright | code',
                        ],
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
                            // Grow the editing area with the text, like a page, instead of an
                            // inner scrollbar. Measured from the body, which cannot feed back
                            // on the iframe height, so it never runs away.
                            editor.on('init', () => {
                                const body = editor.getBody();
                                const frame = editor.iframeElement;
                                const minHeight = 380;
                                if (! body || ! frame) { return; }
                                editor.getContainer().style.height = 'auto';
                                const fit = () => { frame.style.height = Math.max(minHeight, body.offsetHeight + 2) + 'px'; };
                                fit();
                                if (window.ResizeObserver) { new ResizeObserver(fit).observe(body); }
                                editor.on('SetContent ResizeWindow', fit);
                            });
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
