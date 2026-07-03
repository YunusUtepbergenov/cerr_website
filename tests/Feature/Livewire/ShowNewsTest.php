<?php

use App\Livewire\ShowNews;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Livewire\Livewire;

describe('ShowNews Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
        // Clear the shared, non-transactional news:views Redis hash so a leaked
        // news id can't bleed into view-tracking assertions or other suites.
        Redis::del('news:views');
    });

    afterEach(function () {
        Redis::del('news:views');
    });

    it('displays news by slug', function () {
        $news = createNewsWithTranslation(['slug' => 'test-news-slug']);

        Livewire::test(ShowNews::class, ['slug' => 'test-news-slug'])
            ->assertSet('news', fn ($val) => $val->id === $news->id)
            ->assertStatus(200);
    })->group('feature', 'livewire', 'critical');

    it('returns 404 when news not found', function () {
        $response = $this->get(route('show.news', ['slug' => 'non-existent']));

        $response->assertNotFound();
    })->group('feature', 'livewire', 'critical');

    it('returns 404 when news has no translation', function () {
        setAppLocale('uz');
        $news = News::factory()->create(['slug' => 'no-translation']);

        $response = $this->get(route('show.news', ['slug' => 'no-translation']));

        $response->assertNotFound();
    })->group('feature', 'livewire', 'critical');

    it('loads popular news by view count', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        // Create popular news
        $popular1 = createNewsWithTranslation(['view_count' => 100]);
        $popular2 = createNewsWithTranslation(['view_count' => 50]);
        $unpopular = createNewsWithTranslation(['view_count' => 10]);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', function ($val) use ($popular1) {
                return $val->first()->id === $popular1->id;
            });
    })->group('feature', 'livewire', 'critical');

    it('limits popular news to 6 items', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', fn ($val) => $val->count() === 6);
    })->group('feature', 'livewire', 'critical');

    it('popular news respects current locale', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        // News with UZ translation
        $uzNews = createNewsWithTranslation(['view_count' => 100], ['lang' => 'uz']);

        // News with only EN translation (should not appear)
        $enNewsOnly = News::factory()->create(['view_count' => 200]);
        NewsTranslation::factory()->create(['news_id' => $enNewsOnly->id, 'lang' => 'en']);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', fn ($val) => ! $val->contains('id', $enNewsOnly->id));
    })->group('feature', 'livewire', 'critical');

    it('increments redis counter on first visit', function () {
        $news = createNewsWithTranslation(['slug' => 'counted-news']);

        Livewire::test(ShowNews::class, ['slug' => 'counted-news']);

        expect((int) Redis::hget('news:views', $news->id))->toBe(1);
    })->group('feature', 'livewire');

    it('does not increment redis counter on repeated visit in same session', function () {
        $news = createNewsWithTranslation(['slug' => 'repeated-news']);

        Livewire::test(ShowNews::class, ['slug' => 'repeated-news']);
        Livewire::test(ShowNews::class, ['slug' => 'repeated-news']);

        expect((int) Redis::hget('news:views', $news->id))->toBe(1);
    })->group('feature', 'livewire');

    it('popular news ordered by view count descending', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        $news1 = createNewsWithTranslation(['view_count' => 50]);
        $news2 = createNewsWithTranslation(['view_count' => 100]);
        $news3 = createNewsWithTranslation(['view_count' => 75]);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', function ($val) use ($news2, $news) {
                return $val->count() === 4
                    && $val->first()->id === $news2->id
                    && $val->last()->id === $news->id;
            });
    })->group('feature', 'livewire', 'critical');

    it('shows related news sharing a tag and excludes the current article', function () {
        setAppLocale('uz');
        $tag = Tag::factory()->create();

        $main = createNewsWithTranslation(['slug' => 'main', 'category_id' => null]);
        $main->tags()->attach($tag->id);

        $related = createNewsWithTranslation(['slug' => 'related-one']);
        $related->tags()->attach($tag->id);

        $unrelated = createNewsWithTranslation(['slug' => 'unrelated', 'category_id' => null]);

        Livewire::test(ShowNews::class, ['slug' => 'main'])
            ->assertSet('related_news', fn ($v) => $v->contains('id', $related->id)
                && ! $v->contains('id', $main->id)
                && ! $v->contains('id', $unrelated->id));
    })->group('feature', 'livewire');

    it('falls back to same-category news when there are no tag matches', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation();

        $main = createNewsWithTranslation(['slug' => 'main', 'category_id' => $category->id]);
        $sibling = createNewsWithTranslation(['slug' => 'sibling', 'category_id' => $category->id]);

        Livewire::test(ShowNews::class, ['slug' => 'main'])
            ->assertSet('related_news', fn ($v) => $v->contains('id', $sibling->id)
                && ! $v->contains('id', $main->id));
    })->group('feature', 'livewire');

    it('sets the page title from seo_title', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'seo-news'], ['seo_title' => 'Custom SEO Title', 'title' => 'Normal Title']);

        $this->get(route('show.news', 'seo-news'))->assertSee('Custom SEO Title', false);
    })->group('feature', 'livewire');

    it('falls back to the article title when seo_title is empty', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'no-seo'], ['seo_title' => null, 'title' => 'Just The Title']);

        $this->get(route('show.news', 'no-seo'))->assertSee('Just The Title', false);
    })->group('feature', 'livewire');

    it('renders the article body wrapper, preserved styling, tags and related section', function () {
        setAppLocale('uz');
        $tag = Tag::factory()->create(['name' => 'economy']);

        $news = createNewsWithTranslation(
            ['slug' => 'full-render', 'category_id' => null],
            ['content' => '<p style="text-align:center">Centered body copy</p>'],
        );
        $news->tags()->attach($tag->id);

        $related = createNewsWithTranslation(['slug' => 'rel-article']);
        $related->tags()->attach($tag->id);

        $this->get(route('show.news', 'full-render'))
            ->assertSee('news-article-body', false)
            ->assertSee('text-align', false)
            ->assertSee(__('messages.min_read'))
            ->assertSee('economy')
            ->assertSee(__('messages.related_news'));
    })->group('feature', 'livewire');

    it('renders no cover figure when the cover image is missing', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'no-cover'], ['image_url' => '']);

        $this->get(route('show.news', 'no-cover'))
            ->assertOk()
            ->assertDontSee('article-cover', false);
    })->group('feature', 'livewire');

    it('does not render the category badge or share widget', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation();
        $tag = Tag::factory()->create(['name' => 'economy']);

        $news = createNewsWithTranslation(['slug' => 'no-badge-share', 'category_id' => $category->id]);
        $news->tags()->attach($tag->id);

        $this->get(route('show.news', 'no-badge-share'))
            ->assertOk()
            ->assertDontSee('article-category-badge', false)
            ->assertDontSee('article-share', false)
            ->assertDontSee('articleShare', false)
            // category navigation is still available via the breadcrumb, and tags still render
            ->assertSee(route('show.category', $category->slug), false)
            ->assertSee('economy');
    })->group('feature', 'livewire');

    it('renders when the article and sidebar items have null timestamps', function () {
        setAppLocale('uz');
        $tag = Tag::factory()->create();

        $main = createNewsWithTranslation(['slug' => 'legacy-main', 'category_id' => null]);
        $main->tags()->attach($tag->id);

        $related = createNewsWithTranslation(['slug' => 'legacy-related']);
        $related->tags()->attach($tag->id);

        // Legacy/imported news rows can have null created_at/updated_at; the page
        // must render regardless (the meta bar, related grid and popular sidebar
        // all format created_at). Null them directly to bypass Eloquent timestamps.
        DB::table('news')->update(['created_at' => null, 'updated_at' => null]);

        $this->get(route('show.news', 'legacy-main'))->assertOk();
    })->group('feature', 'livewire');

    it('exposes an absolute og:image url for a storage-relative cover', function () {
        setAppLocale('uz');
        // Reproduce the production config where APP_URL has no scheme, so the
        // public disk serves host-relative "/storage/..." URLs (see d241806).
        // Social crawlers require an absolute og:image, so the page must absolutize it.
        config(['filesystems.disks.public.url' => '/storage']);
        createNewsWithTranslation(['slug' => 'og-news'], ['image_url' => 'news/covers/og.jpg']);

        $html = $this->get(route('show.news', 'og-news'))->getContent();

        expect((bool) preg_match('/property="og:image" content="([^"]+)"/', $html, $m))->toBeTrue()
            ->and($m[1])->toStartWith('http');
    })->group('feature', 'livewire');
});
