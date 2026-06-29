<div>
    <x-admin.page-header :title="__('admin.account.title')" :subtitle="__('admin.account.subtitle')" />

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">{{ __('admin.account.profile_title') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="updateProfile">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.account.name') }}</label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" autocomplete="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.account.email') }}</label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" autocomplete="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="updateProfile"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.account.save_profile') }}</span>
                            <span wire:loading wire:target="updateProfile"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">{{ __('admin.account.password_title') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="updatePassword">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.account.current_password') }}</label>
                            <input type="password" wire:model="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="new-password" data-1p-ignore data-lpignore="true">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.account.new_password') }}</label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.account.password_help') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.account.confirm_password') }}</label>
                            <input type="password" wire:model="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="updatePassword"><i class="fa-solid fa-key me-1"></i> {{ __('admin.account.save_password') }}</span>
                            <span wire:loading wire:target="updatePassword"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
