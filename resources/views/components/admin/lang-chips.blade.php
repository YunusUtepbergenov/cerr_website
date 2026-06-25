@props(['available' => []])

<span {{ $attributes->merge(['class' => 'lang-chips']) }}>
    @foreach (['kr', 'uz', 'ru', 'en'] as $locale)
        <span class="lang-chip{{ in_array($locale, $available, true) ? '' : ' missing' }} lang-{{ $locale }}">{{ \App\Support\Locales::label($locale) }}</span>
    @endforeach
</span>
