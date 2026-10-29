<div>
    <x-admin.page-header :title="__('admin.pages.title_section')" :subtitle="__('admin.pages.subtitle')">
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> {{ __('admin.pages.new_page') }}</a>
    </x-admin.page-header>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Slug</th><th>{{ __('admin.pages.title') }}</th><th>{{ __('admin.common.languages') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($pages as $p)
                        @php
                            $available = $p->translations->pluck('language')->all();
                            $pageTitle = $p->translations->firstWhere('language', 'kr')?->title ?? $p->translations->first()?->title;
                            $publicUrl = $p->publicUrl();
                        @endphp
                        <tr wire:key="page-{{ $p->id }}" data-href="{{ route('admin.pages.edit', $p) }}">
                            <td class="text-muted small">#{{ $p->id }}</td>
                            <td><code>{{ $p->slug }}</code></td>
                            <td>
                                <div class="fw-semibold">{{ $pageTitle }}</div>
                                @if ($publicUrl)
                                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="small text-muted text-decoration-none" data-public-url="{{ $p->slug }}">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>{{ parse_url($publicUrl, PHP_URL_PATH) }}
                                    </a>
                                @endif
                            </td>
                            <td><x-admin.lang-chips :available="$available" /></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    @if ($p->isSystemPage())
                                        <span class="btn btn-outline-secondary disabled" title="{{ __('admin.pages.system_page') }}" data-system-page="{{ $p->slug }}"><i class="fa-solid fa-lock"></i></span>
                                    @else
                                        <button class="btn btn-outline-danger" type="button" x-data
                                                @click="$dispatch('open-confirm', { message: @js(__('admin.pages.confirm_delete')), onConfirm: () => $wire.delete({{ $p->id }}) })">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty-state icon="fa-regular fa-file" :title="__('admin.pages.no_pages')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination-footer :paginator="$pages" />
    </div>
</div>
