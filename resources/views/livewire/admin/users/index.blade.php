<div>
    <x-admin.page-header :title="__('admin.users.title_section')" :subtitle="__('admin.users.subtitle')">
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.users.new_user') }}
            </button>
        @endif
    </x-admin.page-header>

    @if ($generatedPassword)
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-key me-1"></i> {{ __('admin.users.reset_password_done', ['password' => $generatedPassword]) }}</span>
            <button class="btn btn-sm btn-light" onclick="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($generatedPassword) }})">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
    @endif

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.users.edit_user') : __('admin.users.create_user') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.name') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.email') }} <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.role') }}</label>
                            <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="admin">{{ __('admin.users.role_admin') }}</option>
                                <option value="writer">{{ __('admin.users.role_writer') }}</option>
                                <option value="editor">{{ __('admin.users.role_editor') }}</option>
                                <option value="viewer">{{ __('admin.users.role_viewer') }}</option>
                                <option value="accountant">{{ __('admin.users.role_accountant') }}</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.password') }} @if (! $editingId) <span class="text-danger">*</span> @endif</label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($editingId)
                                <div class="form-text small">{{ __('admin.users.password_help') }}</div>
                            @endif
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
                        <th>{{ __('admin.users.name') }}</th>
                        <th>{{ __('admin.users.email') }}</th>
                        <th style="width: 130px;">{{ __('admin.users.role') }}</th>
                        <th style="width: 160px;">{{ __('admin.users.last_login') }}</th>
                        <th style="width: 200px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr wire:key="user-{{ $u->id }}">
                            <td class="text-muted small">#{{ $u->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="table-avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td><span class="role-badge role-{{ $u->role }}">{{ __('admin.users.role_'.$u->role) }}</span></td>
                            <td><span class="text-muted small">{{ $u->last_login_at?->diffForHumans() ?? __('admin.users.never_logged_in') }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $u->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.users.confirm_delete')), onConfirm: () => $wire.resetPassword({{ $u->id }}) })"
                                            title="{{ __('admin.users.reset_password') }}"><i class="fa-solid fa-key"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.users.confirm_delete')), onConfirm: () => $wire.delete({{ $u->id }}) })"
                                            title="{{ __('admin.common.delete') }}" @disabled($u->id === auth()->id())><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state icon="fa-regular fa-user" :title="__('admin.users.no_users')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
