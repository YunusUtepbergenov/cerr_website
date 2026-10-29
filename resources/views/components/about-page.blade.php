{{-- Shared layout of the "About the Centre" text pages: navy hero with the
     building photo, tabs to the sibling pages, chapter list, numbered body. --}}
@props(['page', 'active'])

@php($translation = $page->translation)
@php($outline = \App\Support\PageOutline::build($translation->bodyHtml()))
@php($tabs = [
    'about' => 'messages.objectives',
    'history' => 'messages.history',
    'leadership' => 'messages.leadership',
    'structure' => 'messages.structure',
    'open-data.index' => 'messages.open_data',
])

<section class="ab-page">
    <div class="ab-wrap">
        <header class="ab-hero">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">{{ $translation->title }}</span>
            </nav>
            <div class="ab-hero-body">
                <h1 class="ab-title">{{ $translation->title }}</h1>
                <div class="ab-hero-photo" aria-hidden="true">
                    @if ($page->image)
                        <img src="{{ $page->heroImageUrl() }}" alt="" fetchpriority="high" decoding="async">
                    @else
                        <img src="{{ $page->heroImageUrl() }}" srcset="{{ asset('images/research-staff/cerr-building-sm.webp') }} 640w, {{ asset('images/research-staff/cerr-building.webp') }} 1100w" sizes="(max-width: 860px) 100vw, 520px" width="1100" height="696" alt="" fetchpriority="high" decoding="async">
                    @endif
                </div>
            </div>
        </header>

        <nav class="ab-tabs" aria-label="@lang('messages.about')">
            @foreach ($tabs as $route => $label)
                <a href="{{ route($route) }}" wire:navigate class="ab-tab{{ $route === $active ? ' is-active' : '' }}"{!! $route === $active ? ' aria-current="page"' : '' !!}>@lang($label)</a>
            @endforeach
        </nav>

        <div class="ab-content">
            @if (count($outline->chapters) > 1)
                <nav class="ab-toc" aria-label="@lang('messages.on_this_page')">
                    <span class="ab-toc-label">@lang('messages.on_this_page')</span>
                    <ol class="ab-toc-list">
                        @foreach ($outline->chapters as $chapter)
                            <li><a href="#{{ $chapter['id'] }}">{{ $chapter['title'] }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            <div @class(['news-article-body', 'ab-body', 'ab-body--tasks' => $active === 'about'])>
                {!! $outline->html !!}
            </div>
        </div>
    </div>
</section>
