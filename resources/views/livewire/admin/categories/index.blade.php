<div>
    <x-admin.page-header :title="__('admin.categories.title_section')" :subtitle="__('admin.categories.subtitle')">
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.categories.new_category') }}
            </button>
        @endif
    </x-admin.page-header>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.categories.edit_category') : __('admin.categories.create_category') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="press">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.categories.slug_help') }}</div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" id="cat-status" wire:model="status" class="form-check-input">
                                <label for="cat-status" class="form-check-label">{{ __('admin.common.active') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        @foreach (\App\Livewire\Admin\Categories\CategoryIndex::LOCALES as $locale)
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label">
                                    <span class="lang-chip">{{ $locale }}</span> {{ __('admin.categories.name_label') }}
                                    @if ($locale === 'kr') <span class="text-danger">*</span> @endif
                                </label>
                                <input type="text" wire:model="names.{{ $locale }}" class="form-control @error('names.'.$locale) is-invalid @enderror">
                                @error("names.$locale") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="sticky-action-bar">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 180px;">Slug</th>
                        @foreach (\App\Livewire\Admin\Categories\CategoryIndex::LOCALES as $locale)
                            <th><span class="lang-chip">{{ $locale }}</span></th>
                        @endforeach
                        <th style="width: 100px;">{{ __('admin.common.status') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $cat)
                        <tr wire:key="cat-{{ $cat->id }}">
                            <td class="text-muted small">#{{ $cat->id }}</td>
                            <td><code class="small">{{ $cat->slug }}</code></td>
                            @foreach (\App\Livewire\Admin\Categories\CategoryIndex::LOCALES as $locale)
                                @php $name = optional($cat->translations->firstWhere('language', $locale))->name; @endphp
                                <td>{{ $name ?? '—' }}</td>
                            @endforeach
                            <td>
                                @if ($cat->status)
                                    <span class="pill status-published">{{ __('admin.common.active') }}</span>
                                @else
                                    <span class="pill status-draft">{{ __('admin.common.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $cat->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" title="{{ __('admin.common.delete') }}"
                                        x-data
                                        @click="$dispatch('open-confirm', { message: @js(__('admin.categories.confirm_delete')), onConfirm: () => $wire.delete({{ $cat->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state icon="fa-regular fa-folder-open" :title="__('admin.categories.no_categories')">
                                    {{ __('admin.categories.no_categories_help') }}
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
