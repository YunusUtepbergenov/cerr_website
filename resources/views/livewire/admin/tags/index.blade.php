<div>
    <div class="page-header">
        <div>
            <h1>Tags</h1>
            <div class="subtitle">Free-form labels you can attach to news articles.</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> New tag
            </button>
        @endif
    </div>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? 'Edit tag' : 'Create tag' }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="economy">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" wire:click="cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save
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
                        <th>Name</th>
                        <th style="width: 120px;">Articles</th>
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
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $tag->id }})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" wire:click="delete({{ $tag->id }})" wire:confirm="Delete this tag?" title="Delete">
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
                                    <div class="fw-semibold">No tags yet.</div>
                                    <div class="small mt-1">Tags help readers discover related articles.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
