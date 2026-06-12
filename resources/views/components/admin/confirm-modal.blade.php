<div
    x-data="{
        open: false,
        message: '',
        confirmLabel: '{{ __('admin.common.delete') }}',
        cancelLabel: '{{ __('admin.common.cancel') }}',
        callback: null,
        show(detail) {
            this.message = detail.message ?? '{{ __('admin.common.confirm_delete') }}';
            this.confirmLabel = detail.confirmLabel ?? '{{ __('admin.common.delete') }}';
            this.cancelLabel = detail.cancelLabel ?? '{{ __('admin.common.cancel') }}';
            this.callback = detail.onConfirm ?? null;
            this.open = true;
        },
        confirm() {
            if (typeof this.callback === 'function') { this.callback(); }
            this.open = false; this.callback = null;
        },
    }"
    @open-confirm.window="show($event.detail)"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    style="position: fixed; inset: 0; z-index: 1080; background: rgba(15, 23, 42, .5); display: flex; align-items: center; justify-content: center;"
>
    <div @click.outside="open = false" style="background: var(--admin-surface); border-radius: 12px; padding: 1.5rem; max-width: 420px; width: calc(100% - 2rem); box-shadow: 0 20px 40px rgba(15,23,42,.2);">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div style="width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; background: var(--admin-danger-soft); display: flex; align-items: center; justify-content: center; color: var(--admin-danger);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1" style="font-size: 1.05rem;">{{ __('admin.common.confirm_delete') }}</h5>
                <p class="text-muted small mb-0" x-text="message"></p>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="open = false" x-text="cancelLabel"></button>
            <button type="button" class="btn btn-danger" @click="confirm" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
