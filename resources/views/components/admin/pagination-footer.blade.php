@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'card-footer d-flex justify-content-between align-items-center flex-wrap gap-2']) }}>
        <div class="text-muted small">
            {{ __('admin.common.showing_range', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </div>
        <div>{{ $paginator->onEachSide(1)->links() }}</div>
    </div>
@endif
