<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.pages.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.pages.subtitle') }}</div>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> {{ __('admin.pages.new_page') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Slug</th><th>{{ __('admin.common.languages') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($pages as $p)
                        @php $available = $p->translations->pluck('language')->all(); @endphp
                        <tr wire:key="page-{{ $p->id }}">
                            <td class="text-muted small">#{{ $p->id }}</td>
                            <td><code>{{ $p->slug }}</code></td>
                            <td>@foreach (['kr','uz','ru','en'] as $loc)<span class="lang-chip {{ in_array($loc, $available, true) ? '' : 'missing' }}">{{ $loc }}</span>@endforeach</td>
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
                        <tr><td colspan="4"><div class="empty-state"><i class="fa-regular fa-file d-block"></i><div class="fw-semibold">{{ __('admin.pages.no_pages') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
