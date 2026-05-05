<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.tags.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.tags.subtitle') }}</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.tags.new_tag') }}
            </button>
        @endif
    </div>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.tags.edit_tag') : __('admin.tags.create_tag') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('admin.tags.name_label') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="economy">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}
                            </button>
                        </div>
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
                        <th style="width: 80px;">ID</th>
                        <th>{{ __('admin.tags.name_label') }}</th>
                        <th style="width: 120px;">{{ __('admin.tags.articles_count') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $tag)
                        <tr wire:key="tag-{{ $tag->id }}">
                            <td class="text-muted small">#{{ $tag->id }}</td>
                            <td>
                                <span class="lang-chip" style="background:#fef3c7;color:#92400e;">{{ $tag->name }}</span>
                            </td>
                            <td><span class="text-muted small">{{ $tag->news_count }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $tag->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" wire:click="delete({{ $tag->id }})" wire:confirm="{{ __('admin.tags.confirm_delete') }}" title="{{ __('admin.common.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fa-solid fa-tag d-block"></i>
                                    <div class="fw-semibold">{{ __('admin.tags.no_tags') }}</div>
                                    <div class="small mt-1">{{ __('admin.tags.no_tags_help') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
