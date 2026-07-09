<div>
    <x-admin.page-header :title="__('admin.journals.title_section')" :subtitle="__('admin.journals.subtitle')">
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.journals.new_journal') }}
            </button>
        @endif
    </x-admin.page-header>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.journals.edit_journal') : __('admin.journals.create_journal') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.journals.title') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.journals.link') }} <span class="text-danger">*</span></label>
                            <input type="url" wire:model="link" class="form-control @error('link') is-invalid @enderror" placeholder="https://...">
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.journals.published_at') }} <span class="text-danger">*</span></label>
                            <input type="date" wire:model="published_at" class="form-control @error('published_at') is-invalid @enderror">
                            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" wire:model="is_active" id="journal-active" class="form-check-input">
                                <label for="journal-active" class="form-check-label">{{ __('admin.journals.is_active') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('admin.journals.cover') }} @if (! $editingId)<span class="text-danger">*</span>@endif</label>
                            @php
                                $journalPreviewUrl = null;
                                if ($coverUpload) {
                                    try { $journalPreviewUrl = $coverUpload->temporaryUrl(); } catch (\Throwable $e) { $journalPreviewUrl = null; }
                                }
                                if (! $journalPreviewUrl && $cover_image) {
                                    $journalPreviewUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cover_image);
                                }
                            @endphp
                            @if ($journalPreviewUrl)
                                <div class="mb-2"><img src="{{ $journalPreviewUrl }}" alt="" style="width: 120px; height: 160px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);"></div>
                            @endif
                            <input type="file" wire:model="coverUpload" accept="image/*" class="form-control @error('coverUpload') is-invalid @enderror">
                            @error('coverUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="sticky-action-bar">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}</button>
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
                        <th style="width: 70px;">{{ __('admin.journals.cover') }}</th>
                        <th>{{ __('admin.journals.title') }}</th>
                        <th>{{ __('admin.journals.link') }}</th>
                        <th style="width: 120px;">{{ __('admin.journals.published_at') }}</th>
                        <th style="width: 90px;">{{ __('admin.journals.is_active') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($journals as $journal)
                        <tr wire:key="journal-{{ $journal->id }}">
                            <td>@if ($journal->coverUrl())<img src="{{ $journal->coverUrl() }}" alt="" style="width: 44px; height: 58px; object-fit: cover; border-radius: 4px;">@endif</td>
                            <td class="fw-semibold">{{ $journal->title }}</td>
                            <td><a href="{{ $journal->link }}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width: 240px;">{{ $journal->link }}</a></td>
                            <td class="text-muted small">{{ $journal->published_at->format('Y-m-d') }}</td>
                            <td>
                                @if ($journal->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('admin.common.active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('admin.common.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $journal->id }})"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.journals.confirm_delete')), onConfirm: () => $wire.delete({{ $journal->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state icon="fa-solid fa-book-open" :title="__('admin.journals.no_journals')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
