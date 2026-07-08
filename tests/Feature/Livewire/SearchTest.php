<?php

use App\Livewire\Search;
use App\Models\News;
use Livewire\Livewire;

describe('Search Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('renders the search page', function () {
        $this->get(route('search'))->assertStatus(200);
    })->group('feature', 'livewire');

    it('finds published news by title', function () {
        createNewsWithTranslation(['status' => 'published'], ['title' => 'Iqtisodiy islohotlar tahlili']);
        createNewsWithTranslation(['status' => 'published'], ['title' => 'Boshqa mavzu']);

        Livewire::test(Search::class)
            ->set('q', 'islohotlar')
            ->assertSee('Iqtisodiy islohotlar tahlili')
            ->assertDontSee('Boshqa mavzu');
    })->group('feature', 'livewire');

    it('finds news by body content', function () {
        createNewsWithTranslation(['status' => 'published'], [
            'title' => 'Sarlavha',
            'content' => 'Matnda kambag\'allikni qisqartirish dasturi haqida so\'z boradi.',
        ]);

        Livewire::test(Search::class)
            ->set('q', 'kambag\'allikni qisqartirish')
            ->assertSee('Sarlavha');
    })->group('feature', 'livewire');

    it('does not surface unpublished news', function () {
        createNewsWithTranslation(['status' => 'draft'], ['title' => 'Maxfiy qoralama xabari']);

        Livewire::test(Search::class)
            ->set('q', 'qoralama')
            ->assertDontSee('Maxfiy qoralama xabari');
    })->group('feature', 'livewire');

    it('only searches translations in the current locale', function () {
        $news = News::factory()->create(['status' => 'published']);
        $news->translations()->create([
            'lang' => 'ru',
            'title' => 'Русскоязычный заголовок',
            'short_description' => 'x',
            'content' => 'x',
            'image_url' => '1.jpg',
        ]);

        Livewire::test(Search::class)
            ->set('q', 'Русскоязычный')
            ->assertSee(__('messages.nothing_found'));
    })->group('feature', 'livewire');

    it('requires at least two characters before searching', function () {
        createNewsWithTranslation(['status' => 'published'], ['title' => 'A qisqa sarlavha']);

        Livewire::test(Search::class)
            ->set('q', 'A')
            ->assertDontSee('qisqa sarlavha')
            ->assertDontSee(__('messages.search_found'));
    })->group('feature', 'livewire');

    it('treats LIKE wildcards in the query as literal characters', function () {
        createNewsWithTranslation(['status' => 'published'], ['title' => 'Oddiy yangilik matni']);

        Livewire::test(Search::class)
            ->set('q', '%%')
            ->assertDontSee('Oddiy yangilik matni');
    })->group('feature', 'livewire');

    it('loads more results on demand', function () {
        for ($i = 0; $i < 14; $i++) {
            createNewsWithTranslation(['status' => 'published'], ['title' => "Qidiruv natijasi {$i}"]);
        }

        Livewire::test(Search::class)
            ->set('q', 'Qidiruv natijasi')
            ->assertSet('perPage', 12)
            ->call('loadMore')
            ->assertSet('perPage', 24);
    })->group('feature', 'livewire');

    it('handles an array query parameter gracefully', function () {
        $this->get('/search?q[]=x')->assertStatus(200);
        $this->get('/search?q[a][b]=x')->assertStatus(200);
    })->group('feature', 'livewire');

    it('reads the query from the URL', function () {
        createNewsWithTranslation(['status' => 'published'], ['title' => 'URL orqali topilgan xabar']);

        Livewire::withQueryParams(['q' => 'URL orqali'])
            ->test(Search::class)
            ->assertSee('URL orqali topilgan xabar');
    })->group('feature', 'livewire');
});

describe('Layout menu integrity', function () {
    it('renders no development or placeholder links on the home page', function () {
        setAppLocale('uz');

        $html = $this->get('/')->assertStatus(200)->getContent();

        expect($html)->not->toContain('192.168.1.49')
            ->not->toContain('404.html')
            ->not->toContain('unpkg.com')
            ->not->toContain('cer.uz/themes');
    })->group('feature');
});
