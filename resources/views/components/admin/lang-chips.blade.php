@props(['available' => []])

<span {{ $attributes->merge(['class' => 'lang-chips']) }}>
    @foreach (['kr', 'uz', 'ru', 'en'] as $locale)
        <span class="lang-chip{{ in_array($locale, $available, true) ? '' : ' missing' }}">{{ $locale }}</span>
    @endforeach
</span>
