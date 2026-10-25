<?php

use App\Support\ResearchStaffPrototype;

describe('Research staff prototype pages', function () {
    it('lists every research sector with its head and staff', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.index'))->assertOk();

        foreach (ResearchStaffPrototype::SECTORS as $sector) {
            $response->assertSee(__('structure.'.$sector['key']))
                ->assertSee('id="sector-'.$sector['slug'].'"', false);
        }

        $response->assertSee(__('staff.title'))
            ->assertSee(__('staff.head_of_sector'))
            ->assertSee('Абдуллаев Бахтиёр Рустамович')
            ->assertSee(route('research-staff.show', 'bakhtiyor-abdullaev'), false)
            ->assertSee('rs-search', false);
    })->group('feature', 'routes');

    it('renders the listing in every public locale', function (string $locale) {
        setAppLocale($locale);

        $this->get(route('research-staff.index'))
            ->assertOk()
            ->assertSee(__('staff.title', [], $locale));
    })->with(['ru', 'en', 'uz', 'kr'])->group('feature', 'routes');

    it('shows a profile with its provisional sections and sector colleagues', function () {
        setAppLocale('en');

        $this->get(route('research-staff.show', 'bakhtiyor-abdullaev'))
            ->assertOk()
            ->assertSee('Bakhtiyor Abdullaev')
            ->assertSee(__('staff.position_head'))
            ->assertSee(__('staff.interests'))
            ->assertSee(__('staff.education'))
            ->assertSee(__('staff.publications'))
            ->assertSee(__('staff.colleagues'))
            ->assertSee('Nilufar Karimova')
            ->assertSee(route('research-staff.index'), false);
    })->group('feature', 'routes');

    it('returns 404 for an unknown person', function () {
        $this->get(route('research-staff.show', 'nobody-here'))->assertNotFound();
    })->group('feature', 'routes');

    it('is not linked from the public navigation yet', function () {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('research-staff.index'), false);
    })->group('feature', 'routes');

    it('falls back across locales and builds monograms', function () {
        expect(ResearchStaffPrototype::text(['ru' => 'Р', 'en' => 'E'], 'kr'))->toBe('Р')
            ->and(ResearchStaffPrototype::text(['ru' => 'Р', 'en' => 'E'], 'uz'))->toBe('E')
            ->and(ResearchStaffPrototype::text(['ru' => 'Р'], 'en'))->toBe('Р')
            ->and(ResearchStaffPrototype::text([], 'ru'))->toBe('')
            ->and(ResearchStaffPrototype::initials('Абдуллаев Бахтиёр Рустамович'))->toBe('АБ')
            ->and(ResearchStaffPrototype::initials('Bakhtiyor Abdullaev'))->toBe('BA');
    })->group('unit');

    it('gives every person a unique slug and email', function () {
        $people = ResearchStaffPrototype::people();

        expect($people->pluck('slug')->unique())->toHaveCount($people->count())
            ->and($people->pluck('email')->unique())->toHaveCount($people->count())
            ->and(ResearchStaffPrototype::sectors()->every(fn ($sector) => $sector['head'] !== null))->toBeTrue();
    })->group('unit');
});
