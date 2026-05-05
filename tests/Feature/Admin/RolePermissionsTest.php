<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\News\NewsIndex;
use App\Models\News;
use App\Models\User;
use Livewire\Livewire;

describe('viewer role', function () {
    it('cannot access /admin and gets 403', function () {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get('/admin')->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.activity.index'))->assertForbidden();
    })->group('feature', 'admin');
})->group('feature', 'admin');

describe('writer role', function () {
    it('can access /admin/news but not restricted routes', function () {
        $writer = User::factory()->create(['role' => 'writer']);

        $this->actingAs($writer)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($writer)->get(route('admin.news.create'))->assertOk();
        $this->actingAs($writer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($writer)->get(route('admin.activity.index'))->assertForbidden();
    })->group('feature', 'admin');

    it('news index only shows own news', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $other = User::factory()->create(['role' => 'writer']);

        $myNews = News::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);
        $myNews->translations()->create([
            'lang' => 'uz', 'title' => 'My Own Article', 'short_description' => 'desc', 'content' => '<p>x</p>', 'image_url' => '',
        ]);

        $theirNews = News::factory()->create(['user_id' => $other->id, 'status' => 'draft']);
        $theirNews->translations()->create([
            'lang' => 'uz', 'title' => 'Someone Elses Article', 'short_description' => 'desc', 'content' => '<p>y</p>', 'image_url' => '',
        ]);

        $this->actingAs($writer);

        Livewire::test(NewsIndex::class)
            ->assertSee('My Own Article')
            ->assertDontSee('Someone Elses Article');
    })->group('feature', 'admin');

    it('cannot edit someone elses news via NewsForm (403)', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $other = User::factory()->create(['role' => 'admin']);
        $news = News::factory()->create(['user_id' => $other->id]);

        $this->actingAs($writer)
            ->get(route('admin.news.edit', $news))
            ->assertForbidden();
    })->group('feature', 'admin');

    it('status is forced to draft on save regardless of input', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $news = News::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);

        $this->actingAs($writer);

        Livewire::test(NewsForm::class, ['news' => $news])
            ->set('status', 'published')
            ->set('translations.uz.title', 'Test title')
            ->set('translations.uz.short_description', 'Short desc')
            ->set('translations.uz.content', '<p>Content</p>')
            ->call('save');

        expect($news->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('cannot bulkPublish (aborts 403)', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $news = News::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);

        $this->actingAs($writer);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$news->id])
            ->call('bulkPublish')
            ->assertForbidden();

        expect($news->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('cannot bulkUnpublish (aborts 403)', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $news = News::factory()->create(['user_id' => $writer->id, 'status' => 'published']);

        $this->actingAs($writer);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$news->id])
            ->call('bulkUnpublish')
            ->assertForbidden();

        expect($news->fresh()->status)->toBe('published');
    })->group('feature', 'admin');

    it('cannot delete someone elses news via delete action', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        $other = User::factory()->create(['role' => 'admin']);
        $news = News::factory()->create(['user_id' => $other->id]);

        $this->actingAs($writer);

        Livewire::test(NewsIndex::class)
            ->call('delete', $news->id)
            ->assertForbidden();

        expect(News::find($news->id))->not->toBeNull();
    })->group('feature', 'admin');
})->group('feature', 'admin');

describe('editor role', function () {
    it('can access news, pages, categories, activity but not users', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.activity.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    })->group('feature', 'admin');
})->group('feature', 'admin');

describe('admin role', function () {
    it('can access every admin route', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    })->group('feature', 'admin');

    it('can edit any news regardless of owner', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'writer']);
        $news = News::factory()->create(['user_id' => $other->id]);

        $this->actingAs($admin)
            ->get(route('admin.news.edit', $news))
            ->assertOk();
    })->group('feature', 'admin');
})->group('feature', 'admin');
