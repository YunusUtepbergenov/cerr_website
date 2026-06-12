<div>
    <x-admin.page-header :title="__('admin.pages.title_section')" :subtitle="__('admin.pages.subtitle')">
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> {{ __('admin.pages.new_page') }}</a>
    </x-admin.page-header>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Slug</th><th>{{ __('admin.common.languages') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($pages as $p)
                        @php $available = $p->translations->pluck('language')->all(); @endphp
                        <tr wire:key="page-{{ $p->id }}" data-href="{{ route('admin.pages.edit', $p) }}">
                            <td class="text-muted small">#{{ $p->id }}</td>
                            <td><code>{{ $p->slug }}</code></td>
                            <td><x-admin.lang-chips :available="$available" /></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.pages.confirm_delete')), onConfirm: () => $wire.delete({{ $p->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-admin.empty-state icon="fa-regular fa-file" :title="__('admin.pages.no_pages')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
