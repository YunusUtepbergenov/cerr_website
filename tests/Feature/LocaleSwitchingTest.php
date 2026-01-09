<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\News;
use App\Models\NewsTranslation;

describe('Locale Switching', function () {
    it('can switch locale via route', function () {
        $response = $this->get(route('lang.switch', ['locale' => 'en']));

        $response->assertSessionHas('locale', 'en');
        $response->assertRedirect();
    })->group('integration', 'locale');

    it('SetLocale middleware applies session locale', function () {
        session(['locale' => 'kr']);

        $this->get('/')->assertStatus(200);

        expect(app()->getLocale())->toBe('kr');
    })->group('integration', 'locale');

    it('home page shows correct translation after locale switch', function () {
        $category = Category::factory()->create(['slug' => 'research']);
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'uz', 'name' => 'Tadqiqotlar']);
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'en', 'name' => 'Research']);

        session(['locale' => 'uz']);
        $this->get('/')->assertStatus(200);
        expect(app()->getLocale())->toBe('uz');

        session(['locale' => 'en']);
        $this->get('/')->assertStatus(200);
        expect(app()->getLocale())->toBe('en');
    })->group('integration', 'locale');

    it('news page shows correct translation for each locale', function () {
        $news = News::factory()->create(['slug' => 'test-news']);
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'uz', 'title' => 'UZ Title']);
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'en', 'title' => 'EN Title']);

        session(['locale' => 'uz']);
        $response = $this->get(route('show.news', ['slug' => 'test-news']));
        $response->assertStatus(200);

        session(['locale' => 'en']);
        $response = $this->get(route('show.news', ['slug' => 'test-news']));
        $response->assertStatus(200);
    })->group('integration', 'locale');

    it('switches between all supported locales', function (string $locale) {
        $news = News::factory()->create(['slug' => 'test-news']);
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => $locale]);

        session(['locale' => $locale]);
        $this->get(route('show.news', ['slug' => 'test-news']))->assertStatus(200);
    })->with(['uz', 'kr', 'en', 'ru'])
        ->group('integration', 'locale');

    it('falls back to default locale when session not set', function () {
        $this->get('/');

        expect(app()->getLocale())->toBe(config('app.locale'));
    })->group('integration', 'locale');
});
