<div>
    <livewire:admin.media.media-picker />

    <div class="page-header">
        <div>
            <h1>{{ $page?->exists ? __('admin.pages.edit_page') : __('admin.pages.create_page') }}</h1>
            <div class="subtitle">{{ $page?->exists ? '#'.$page->id : __('admin.pages.create_page') }}</div>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('admin.common.back') }}
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <ul class="nav nav-tabs lang-tabs">
                            @foreach (\App\Livewire\Admin\Pages\PageForm::LOCALES as $locale)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $activeLocale === $locale ? 'active' : '' }}" wire:click.prevent="setLocale('{{ $locale }}')">
                                        {{ strtoupper($locale) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        @foreach (\App\Livewire\Admin\Pages\PageForm::LOCALES as $locale)
                            <div @class(['d-none' => $activeLocale !== $locale])>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.pages.title') }} @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <input type="text" wire:model="translations.{{ $locale }}.title" class="form-control @error('translations.'.$locale.'.title') is-invalid @enderror">
                                    @error("translations.$locale.title") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.pages.content') }} @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <div wire:ignore>
                                        <textarea
                                            id="page-editor-{{ $locale }}"
                                            class="tinymce-editor"
                                            data-locale="{{ $locale }}">{!! $translations[$locale]['content'] ?? '' !!}</textarea>
                                    </div>
                                    @error("translations.$locale.content") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <details class="mb-3">
                                    <summary class="text-muted small mb-2" style="cursor: pointer;">SEO ({{ strtoupper($locale) }})</summary>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_title') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_title" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_description') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_description" class="form-control">
                                        </div>
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3" style="position: sticky; top: 80px;">
                    <div class="card-header">{{ __('admin.news.publishing') }}</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.pages.slug') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.pages.slug_help') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.pages.image') }}</label>
                            @php
                                $pagePreviewUrl = null;
                                if ($imageUpload) {
                                    try { $pagePreviewUrl = $imageUpload->temporaryUrl(); } catch (\Throwable $e) { $pagePreviewUrl = null; }
                                }
                                if (! $pagePreviewUrl && $image) {
                                    $pagePreviewUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($image);
                                }
                            @endphp
                            @if ($pagePreviewUrl)
                                <div class="mb-2"><img src="{{ $pagePreviewUrl }}" alt="" style="max-width: 100%; border-radius: 6px; border: 1px solid var(--admin-border-soft);"></div>
                            @endif
                            <div x-data x-on:media-picked.window="(e) => $wire.set('image', e.detail.path)">
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-2" @click="$dispatch('show-picker', { folder: 'pages' })"><i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}</button>
                                <input type="file" wire:model="imageUpload" accept="image/*" class="form-control @error('imageUpload') is-invalid @enderror">
                                @error('imageUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (function () {
                const initEditor = (textarea) => {
                    if (!window.tinymce || textarea.dataset.tmceInitialized === '1') return;
                    textarea.dataset.tmceInitialized = '1';
                    const locale = textarea.dataset.locale;
                    window.tinymce.init({
                        target: textarea,
                        height: 480,
                        menubar: false,
                        promotion: false,
                        branding: false,
                        plugins: 'lists link image table code autolink',
                        toolbar: 'undo redo | styles | bold italic | bullist numlist | link image table | code',
                        setup: (editor) => {
                            editor.on('change input keyup', () => {
                                window.Livewire.find('{{ $this->getId() }}').set('translations.' + locale + '.content', editor.getContent(), false);
                            });
                        },
                    });
                };
                const initAll = () => document.querySelectorAll('textarea.tinymce-editor').forEach(initEditor);
                if (document.readyState !== 'loading') initAll(); else document.addEventListener('DOMContentLoaded', initAll);
                document.addEventListener('livewire:navigated', initAll);
            })();
        </script>
    @endpush
</div>
