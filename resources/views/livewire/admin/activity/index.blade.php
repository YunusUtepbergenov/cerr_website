<div>
    <x-admin.page-header :title="__('admin.activity.title_section')" :subtitle="__('admin.activity.subtitle')" />

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <select wire:model.live="action" class="form-select">
                        <option value="">{{ __('admin.common.all') }}</option>
                        <option value="created">{{ __('admin.activity.action_created') }}</option>
                        <option value="updated">{{ __('admin.activity.action_updated') }}</option>
                        <option value="deleted">{{ __('admin.activity.action_deleted') }}</option>
                        <option value="published">{{ __('admin.activity.action_published') }}</option>
                        <option value="unpublished">{{ __('admin.activity.action_unpublished') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select wire:model.live="subjectType" class="form-select">
                        <option value="">{{ __('admin.common.all') }}</option>
                        <option value="App\Models\News">{{ __('admin.nav.news') }}</option>
                        <option value="App\Models\Page">{{ __('admin.nav.pages') }}</option>
                        <option value="App\Models\Video">{{ __('admin.nav.videos') }}</option>
                        <option value="App\Models\User">{{ __('admin.nav.users') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('admin.activity.when') }}</th>
                        <th>{{ __('admin.activity.who') }}</th>
                        <th>{{ __('admin.activity.what') }}</th>
                        <th>{{ __('admin.activity.subject') }}</th>
                        <th>{{ __('admin.activity.changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $a)
                        <tr wire:key="activity-{{ $a->id }}">
                            <td><span class="text-muted small">{{ $a->created_at->diffForHumans() }}</span></td>
                            <td>{{ $a->user->name ?? __('admin.activity.system') }}</td>
                            <td>{{ __('admin.activity.action_'.$a->action) }}</td>
                            <td><code class="small">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</code></td>
                            <td><code class="small">{{ json_encode($a->changes ?? [], JSON_UNESCAPED_UNICODE) }}</code></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty-state icon="fa-solid fa-clock-rotate-left" :title="__('admin.activity.no_activity')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activities->hasPages())
            <div class="card-footer">{{ $activities->onEachSide(1)->links() }}</div>
        @endif
    </div>
</div>
