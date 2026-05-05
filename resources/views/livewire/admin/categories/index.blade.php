<div>
    <div class="page-header">
        <div>
            <h1>Categories</h1>
            <div class="subtitle">Group news articles. Each category supports 4 language names.</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> New category
            </button>
        @endif
    </div>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? 'Edit category' : 'Create category' }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="press">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Used in the category URL.</div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" id="cat-status" wire:model="status" class="form-check-input">
                                <label for="cat-status" class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        @foreach (\App\Livewire\Admin\Categories\CategoryIndex::LOCALES as $locale)
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label">
                                    <span class="lang-chip">{{ $locale }}</span> Name
                                    @if ($locale === 'kr') <span class="text-danger">*</span> @endif
                                </label>
                                <input type="text" wire:model="names.{{ $locale }}" class="form-control @error('names.'.$locale) is-invalid @enderror">
                                @error("names.$locale") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="sticky-action-bar">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save
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
                        <th style="width: 100px;">Status</th>
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
                                    <span class="pill status-published">Active</span>
                                @else
                                    <span class="pill status-draft">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $cat->id }})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" wire:click="delete({{ $cat->id }})" wire:confirm="Delete this category? News in it will be uncategorized." title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa-regular fa-folder-open d-block"></i>
                                    <div class="fw-semibold">No categories yet.</div>
                                    <div class="small mt-1">Create one to start grouping articles.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
