<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/about-pages.css') }}?v={{ filemtime(public_path('css/about-pages.css')) }}">
    @endpush

    <x-about-page :page="$page" active="about" />
</div>
