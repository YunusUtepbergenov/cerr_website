<div>
    <livewire:admin.media.media-picker />

    <div class="page-header">
        <div>
            <h1>{{ __('admin.videos.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.videos.subtitle') }}</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.videos.new_video') }}
            </button>
        @endif
    </div>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.videos.edit_video') : __('admin.videos.create_video') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.videos.title') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.videos.url') }} <span class="text-danger">*</span></label>
                            <input type="url" wire:model="url" class="form-control @error('url') is-invalid @enderror" placeholder="https://...">
                            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('admin.videos.image') }}</label>
                            @if ($image)
                                <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="" style="max-width: 220px; border-radius: 6px; border: 1px solid var(--admin-border-soft);"></div>
                            @endif
                            <div x-data x-on:media-picked.window="(e) => $wire.set('image', e.detail.path)">
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-2" @click="$dispatch('show-picker', { folder: 'videos' })"><i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}</button>
                                <input type="file" wire:model="imageUpload" accept="image/*" class="form-control @error('imageUpload') is-invalid @enderror">
                                @error('imageUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
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
                        <th style="width: 60px;">ID</th>
                        <th style="width: 90px;">{{ __('admin.videos.image') }}</th>
                        <th>{{ __('admin.videos.title') }}</th>
                        <th>{{ __('admin.videos.url') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($videos as $v)
                        <tr wire:key="video-{{ $v->id }}">
                            <td class="text-muted small">#{{ $v->id }}</td>
                            <td>@if ($v->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($v->image) }}" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">@endif</td>
                            <td class="fw-semibold">{{ $v->title }}</td>
                            <td><a href="{{ $v->url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 240px;">{{ $v->url }}</a></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $v->id }})"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.videos.confirm_delete')), onConfirm: () => $wire.delete({{ $v->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-video d-block"></i><div class="fw-semibold">{{ __('admin.videos.no_videos') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
