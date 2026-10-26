<?php

use App\Support\ResearchStaffPrototype;

describe('Research staff prototype pages', function () {
    it('lists every research sector as a card linking to its own page', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.index'))->assertOk();

        foreach (ResearchStaffPrototype::SECTORS as $sector) {
            $response->assertSee(ResearchStaffPrototype::sectorName($sector['key']))
                ->assertSee(route('research-staff.sector', $sector['slug']), false);
        }

        $response->assertSee(__('staff.title'))
            ->assertSee(__('staff.see_more'))
            ->assertSee('rs-hero', false)
            ->assertSeeInOrder(['rs-hero', 'article-breadcrumb', 'rs-stats', 'rs-note', 'rs-sector-grid'], false)
            ->assertSee('rs-sector-grid', false)
            ->assertSee('rs-stack-item', false)
            ->assertSee('fa-chart-line', false)
            ->assertSee(__('staff.sector_short_macro'))
            ->assertSee(__('staff.sector_desc_macro'))
            ->assertSee('Абдуллаев Бахтиёр Рустамович')
            ->assertSee(__('staff.head_of_sector'))
            ->assertDontSee('rs-side', false)
            ->assertDontSee(route('research-staff.show', 'bakhtiyor-abdullaev'), false);

        expect(substr_count($response->getContent(), 'data-sector-card="'))->toBe(count(ResearchStaffPrototype::SECTORS));
    })->group('feature', 'routes');

    it('shows a sector page with its head first, then staff, and the sector list', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.sector', 'macroeconomic-policy'))->assertOk();

        $response->assertSee(__('structure.macro'))
            ->assertSee(__('staff.staff_of_sector'))
            ->assertSee(__('staff.head_of_sector'))
            ->assertSeeInOrder(['Абдуллаев Бахтиёр Рустамович', 'Каримова Нилуфар Шавкатовна', 'Тошматов Отабек Улугбекович'])
            ->assertSee(route('research-staff.show', 'bakhtiyor-abdullaev'), false)
            ->assertSee('mailto:b.abdullaev@cerr.uz', false)
            ->assertSee(route('research-staff.sector', 'regional-development'), false)
            ->assertSee('fa-chart-line', false)
            ->assertSee('01 / 14')
            ->assertSeeInOrder(['rs-hero-head', 'Абдуллаев Бахтиёр Рустамович', 'rs-stats', 'rs-note', 'rs-people'], false);

        expect(substr_count($response->getContent(), 'data-person="'))->toBe(5);
    })->group('feature', 'routes');

    it('returns 404 for an unknown sector', function () {
        $this->get(route('research-staff.sector', 'no-such-sector'))->assertNotFound();
    })->group('feature', 'routes');

    it('renders the listing in every public locale', function (string $locale) {
        setAppLocale($locale);

        $this->get(route('research-staff.index'))
            ->assertOk()
            ->assertSee(__('staff.title', [], $locale));
    })->with(['ru', 'en', 'uz', 'kr'])->group('feature', 'routes');

    it('shows a profile structured like the staff questionnaire', function () {
        setAppLocale('en');

        $this->get(route('research-staff.show', 'bakhtiyor-abdullaev'))
            ->assertOk()
            ->assertSee('Bakhtiyor Abdullaev')
            ->assertSee(__('staff.position_head'))
            ->assertSeeInOrder([
                __('staff.part_education'),
                __('staff.bachelor'), __('staff.master'), __('staff.doctorate'), __('staff.degree_title'),
                __('staff.part_research'),
                __('staff.research_areas'), __('staff.international'), __('staff.achievements'), __('staff.previous_experience'),
                __('staff.part_publications'),
                __('staff.articles'), __('staff.books'), __('staff.reports'),
            ])
            ->assertSee('Doctor of Economics (DSc)')
            ->assertSee('https://orcid.org/0000-0000-0000-0000', false)
            ->assertSee('rs-profile-link--scholar', false)
            ->assertSee('</i> DOI', false)
            ->assertSee(trans_choice('staff.publications_count', 6))
            ->assertSee(__('staff.colleagues'))
            ->assertSee('Nilufar Karimova')
            ->assertSee(route('research-staff.sector', 'macroeconomic-policy'), false)
            ->assertSee(route('research-staff.index'), false);
    })->group('feature', 'routes');

    it('skips questionnaire sections a person left empty', function () {
        setAppLocale('ru');

        $this->get(route('research-staff.show', 'otabek-toshmatov'))
            ->assertOk()
            ->assertSee(__('staff.bachelor'))
            ->assertSee(__('staff.research_areas'))
            ->assertDontSee(__('staff.master'))
            ->assertDontSee(__('staff.part_publications'))
            ->assertDontSee('rs-profiles', false);
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
            ->and(ResearchStaffPrototype::text('As is', 'kr'))->toBe('As is')
            ->and(ResearchStaffPrototype::items(['ru' => ['а', 'б'], 'en' => ['a']], 'uz'))->toBe(['a'])
            ->and(ResearchStaffPrototype::items(['ru' => ['а', 'б']], 'en'))->toBe(['а', 'б'])
            ->and(ResearchStaffPrototype::initials('Абдуллаев Бахтиёр Рустамович'))->toBe('АБ')
            ->and(ResearchStaffPrototype::initials('Bakhtiyor Abdullaev'))->toBe('BA');
    })->group('unit');

    it('names every sector, from the structure strings or its own placeholders', function () {
        setAppLocale('ru');

        expect(ResearchStaffPrototype::SECTORS)->toHaveCount(14)
            ->and(ResearchStaffPrototype::sectorName('macro'))->toBe(__('structure.macro'))
            ->and(ResearchStaffPrototype::sectorName('labour'))->toBe(__('staff.sector_name_labour'))
            ->and(ResearchStaffPrototype::sectorName('labour'))->not->toContain('staff.')
            ->and(ResearchStaffPrototype::findSector('macroeconomic-policy')['head']['slug'])->toBe('bakhtiyor-abdullaev')
            ->and(ResearchStaffPrototype::findSector('nope'))->toBeNull();
    })->group('unit');

    it('gives every person a unique slug, an email and the questionnaire fields', function () {
        $people = ResearchStaffPrototype::people();
        $fields = [...ResearchStaffPrototype::EDUCATION_FIELDS, ...ResearchStaffPrototype::ACTIVITY_FIELDS, ...ResearchStaffPrototype::PUBLICATION_FIELDS, 'research_areas', 'profiles'];

        expect($people->pluck('slug')->unique())->toHaveCount($people->count())
            ->and($people->pluck('email')->unique())->toHaveCount($people->count())
            ->and($people->every(fn (array $person) => count(array_diff($fields, array_keys($person))) === 0))->toBeTrue()
            ->and(ResearchStaffPrototype::publicationCount($people->firstWhere('slug', 'bakhtiyor-abdullaev')))->toBe(6)
            ->and(ResearchStaffPrototype::sectors()->every(fn ($sector) => $sector['head'] !== null))->toBeTrue();
    })->group('unit');
});
