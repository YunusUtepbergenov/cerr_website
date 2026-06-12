<div>
    <x-admin.page-header :title="__('admin.tags.title_section')" :subtitle="__('admin.tags.subtitle')">
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.tags.new_tag') }}
            </button>
        @endif
    </x-admin.page-header>

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
                                <span class="lang-chip" style="background: var(--admin-warning-soft); color: var(--admin-warning);">{{ $tag->name }}</span>
                            </td>
                            <td><span class="text-muted small">{{ $tag->news_count }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $tag->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" title="{{ __('admin.common.delete') }}"
                                        x-data
                                        @click="$dispatch('open-confirm', { message: @js(__('admin.tags.confirm_delete')), onConfirm: () => $wire.delete({{ $tag->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state icon="fa-solid fa-tag" :title="__('admin.tags.no_tags')">
                                    {{ __('admin.tags.no_tags_help') }}
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
