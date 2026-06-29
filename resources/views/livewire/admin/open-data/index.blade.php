<div>
    <x-admin.page-header :title="__('admin.open_data.title_section')" :subtitle="__('admin.open_data.subtitle')">
        @unless ($showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.open_data.new_entry') }}
            </button>
        @endunless
    </x-admin.page-header>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.open_data.edit_entry') : __('admin.open_data.new_entry') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <ul class="nav nav-tabs lang-tabs">
                        @foreach (\App\Livewire\Admin\OpenData\OpenDataIndex::LOCALES as $locale)
                            <li class="nav-item">
                                <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#od-tab-{{ $locale }}">
                                    {{ \App\Support\Locales::label($locale) }}
                                    @if ($locale === \App\Livewire\Admin\OpenData\OpenDataIndex::PRIMARY_LOCALE) <span class="text-danger">*</span> @endif
                                    @error('titles.'.$locale) <i class="fa-solid fa-circle-exclamation text-danger lang-status"></i> @enderror
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content mb-3">
                        @foreach (\App\Livewire\Admin\OpenData\OpenDataIndex::LOCALES as $locale)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="od-tab-{{ $locale }}">
                                <label class="form-label">{{ __('admin.open_data.title') }} ({{ \App\Support\Locales::label($locale) }})</label>
                                <input type="text" wire:model="titles.{{ $locale }}" class="form-control @error('titles.'.$locale) is-invalid @enderror">
                                @error('titles.'.$locale) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.open_data.year') }} <span class="text-danger">*</span></label>
                            <input type="number" wire:model="year" class="form-control @error('year') is-invalid @enderror" min="2000" max="2100">
                            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.open_data.quarter') }}</label>
                            <select wire:model="quarter" class="form-select">
                                <option value="">{{ __('admin.open_data.quarter_none') }}</option>
                                <option value="1">I</option>
                                <option value="2">II</option>
                                <option value="3">III</option>
                                <option value="4">IV</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.open_data.file') }} @unless ($editingId) <span class="text-danger">*</span> @endunless</label>
                            <input type="file" wire:model="fileUpload" class="form-control @error('fileUpload') is-invalid @enderror">
                            @error('fileUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.open_data.file_help') }}</div>
                            @if ($editingId && $currentFileName)
                                <div class="form-text small">{{ __('admin.open_data.current_file') }}: {{ $currentFileName }}</div>
                            @endif
                            <div wire:loading wire:target="fileUpload" class="text-muted small mt-1">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.uploading') }}
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input type="checkbox" wire:model="is_published" id="od_published" class="form-check-input">
                        <label for="od_published" class="form-check-label">{{ __('admin.open_data.published') }}</label>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
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
                        <th>{{ __('admin.open_data.title') }}</th>
                        <th style="width: 120px;">{{ __('admin.open_data.year') }}</th>
                        <th style="width: 90px;">{{ __('admin.open_data.file') }}</th>
                        <th style="width: 120px;">{{ __('admin.open_data.download_count') }}</th>
                        <th style="width: 120px;">{{ __('admin.common.status') }}</th>
                        <th style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr wire:key="od-{{ $entry->id }}">
                            <td class="fw-semibold">{{ $entry->title() ?: '—' }}</td>
                            <td>{{ $entry->year }}@if ($entry->quarter) · {{ $entry->quarterLabel() }}@endif</td>
                            <td><span class="lang-chip">{{ $entry->fileExtension() }}</span></td>
                            <td><span class="text-muted small"><i class="fa-solid fa-download me-1"></i>{{ $entry->download_count }}</span></td>
                            <td><x-admin.status-pill :status="$entry->is_published ? 'published' : 'draft'" /></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" wire:click="edit({{ $entry->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-outline-danger" title="{{ __('admin.common.delete') }}"
                                        x-data @click="$dispatch('open-confirm', { message: @js(__('admin.open_data.confirm_delete')), onConfirm: () => $wire.delete({{ $entry->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state icon="fa-regular fa-folder-open" :title="__('admin.open_data.no_entries')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    {{ __('admin.common.showing_range', ['from' => $entries->firstItem(), 'to' => $entries->lastItem(), 'total' => $entries->total()]) }}
                </div>
                <div>{{ $entries->onEachSide(1)->links() }}</div>
            </div>
        @endif
    </div>
</div>
