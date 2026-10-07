<?php

use App\Livewire\Admin\Categories\CategoryIndex;
use App\Livewire\Admin\Journals\JournalIndex;
use App\Livewire\Admin\Media\MediaIndex;
use App\Livewire\Admin\Pages\PageIndex;
use App\Livewire\Admin\Tags\TagIndex;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\Category;
use App\Models\Journal;
use App\Models\Page;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Admin list pagination', function () {
    it('paginates each admin list at its page size', function (string $component, string $viewKey, Closure $seed) {
        $perPage = $component::PER_PAGE;
        $seed($perPage + 1);

        Livewire::test($component)
            ->assertViewHas($viewKey, fn ($list) => $list instanceof LengthAwarePaginator
                && $list->count() === $perPage
                && $list->total() >= $perPage + 1
                && $list->hasPages());
    })->with([
        'tags' => [TagIndex::class, 'tags', fn (int $n) => Tag::factory()->count($n)->create()],
        'categories' => [CategoryIndex::class, 'categories', fn (int $n) => Category::factory()->count($n)->create()],
        'journals' => [JournalIndex::class, 'journals', fn (int $n) => Journal::factory()->count($n)->create()],
        'pages' => [PageIndex::class, 'pages', fn (int $n) => Page::factory()->count($n)->create()],
        'users' => [UserIndex::class, 'users', fn (int $n) => User::factory()->count($n)->create()],
        'videos' => [VideoIndex::class, 'videos', fn (int $n) => Video::factory()->count($n)->create()],
    ])->group('feature', 'admin', 'pagination');

    it('serves the second page of tags', function () {
        Tag::factory()->count(TagIndex::PER_PAGE + 1)->create();

        Livewire::withQueryParams(['page' => 2])
            ->test(TagIndex::class)
            ->assertViewHas('tags', fn ($tags) => $tags->currentPage() === 2 && $tags->count() === 1);
    })->group('feature', 'admin', 'pagination');

    it('filters tags by name and resets to the first page', function () {
        Tag::factory()->count(TagIndex::PER_PAGE + 1)->create();
        Tag::factory()->create(['name' => 'zzz-unique-needle']);

        Livewire::withQueryParams(['page' => 2])
            ->test(TagIndex::class)
            ->set('search', 'unique-needle')
            ->assertSee('zzz-unique-needle')
            ->assertViewHas('tags', fn ($tags) => $tags->total() === 1 && $tags->currentPage() === 1);
    })->group('feature', 'admin', 'pagination');

    it('paginates the media library across managed folders', function () {
        Storage::fake('public');
        foreach (range(1, MediaIndex::PER_PAGE + 1) as $i) {
            Storage::disk('public')->put(sprintf('news/covers/file-%02d.jpg', $i), 'fake');
        }

        Livewire::test(MediaIndex::class)
            ->assertViewHas('files', fn ($files) => $files instanceof LengthAwarePaginator
                && $files->count() === MediaIndex::PER_PAGE
                && $files->total() === MediaIndex::PER_PAGE + 1);

        Livewire::withQueryParams(['page' => 2])
            ->test(MediaIndex::class)
            ->assertViewHas('files', fn ($files) => $files->currentPage() === 2 && $files->count() === 1);
    })->group('feature', 'admin', 'pagination');

    it('renders the pagination footer only when there is more than one page', function () {
        Tag::factory()->count(3)->create();

        Livewire::test(TagIndex::class)->assertDontSee('page-item');

        Tag::factory()->count(TagIndex::PER_PAGE)->create();

        Livewire::test(TagIndex::class)->assertSee('page-item');
    })->group('feature', 'admin', 'pagination');
});
