<?php

use App\Livewire\Home;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Livewire\Livewire;

beforeEach(function () {
    setAppLocale('uz');
    $this->researchCategory = createCategoryWithTranslation(['slug' => 'research']);
});

describe('News status visibility', function () {
    it('hides draft news from the home page', function () {
        $news = createNewsWithTranslation(['category_id' => $this->researchCategory->id, 'status' => 'draft']);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->isEmpty());
    })->group('feature', 'public');

    it('shows published news on the home page', function () {
        createNewsWithTranslation(['category_id' => $this->researchCategory->id, 'status' => 'published']);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->count() === 1);
    })->group('feature', 'public');

    it('hides disabled news', function () {
        createNewsWithTranslation(['category_id' => $this->researchCategory->id, 'status' => 'disabled']);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->isEmpty());
    })->group('feature', 'public');

    it('hides auto_publish news whose scheduled_at is in the future', function () {
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'auto_publish',
            'scheduled_at' => now()->addHour(),
        ]);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->isEmpty());
    })->group('feature', 'public');

    it('shows auto_publish news whose scheduled_at has passed', function () {
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'auto_publish',
            'scheduled_at' => now()->subHour(),
        ]);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->count() === 1);
    })->group('feature', 'public');

    it('returns 404 when accessing a draft news directly', function () {
        $news = createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'draft',
            'slug' => 'secret-draft',
        ]);

        $this->get('/show-news/secret-draft')->assertNotFound();
    })->group('feature', 'public');

    it('returns 200 when accessing a published news directly', function () {
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'published',
            'slug' => 'public-article',
        ]);

        $this->get('/show-news/public-article')->assertOk();
    })->group('feature', 'public');

    it('lets admins preview a draft directly', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'draft',
            'slug' => 'admin-preview-draft',
        ]);

        $this->actingAs($admin)->get('/show-news/admin-preview-draft')->assertOk();
    })->group('feature', 'public');

    it('still hides drafts from users without edit rights', function () {
        $viewer = User::factory()->create(['role' => 'viewer']);
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'draft',
            'slug' => 'viewer-blocked-draft',
        ]);

        $this->actingAs($viewer)->get('/show-news/viewer-blocked-draft')->assertNotFound();
    })->group('feature', 'public');

    it('does not count views for unpublished previews', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation([
            'category_id' => $this->researchCategory->id,
            'status' => 'draft',
            'slug' => 'untracked-draft',
        ]);

        Redis::shouldReceive('hincrby')->never();

        $this->actingAs($admin)->get('/show-news/untracked-draft')->assertOk();
    })->group('feature', 'public');
});

describe('news:promote-scheduled command', function () {
    it('flips ripe auto_publish to published', function () {
        $ripe = News::factory()->create([
            'status' => 'auto_publish',
            'scheduled_at' => now()->subMinute(),
        ]);
        $future = News::factory()->create([
            'status' => 'auto_publish',
            'scheduled_at' => now()->addHour(),
        ]);
        $alreadyPublished = News::factory()->create(['status' => 'published']);

        $this->artisan('news:promote-scheduled')->assertExitCode(0);

        expect($ripe->fresh()->status)->toBe('published');
        expect($future->fresh()->status)->toBe('auto_publish');
        expect($alreadyPublished->fresh()->status)->toBe('published');
    })->group('feature', 'console');
});
