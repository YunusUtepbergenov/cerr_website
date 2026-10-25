<?php

use App\Support\ResearchStaffPrototype;

describe('Research staff prototype pages', function () {
    it('lists every research sector with its head and staff', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.index'))->assertOk();

        foreach (ResearchStaffPrototype::SECTORS as $sector) {
            $response->assertSee(ResearchStaffPrototype::sectorName($sector['key']))
                ->assertSee('id="sector-'.$sector['slug'].'"', false)
                ->assertSee('data-index="'.$sector['slug'].'"', false);
        }

        $response->assertSee(__('staff.title'))
            ->assertSee(__('staff.head_of_sector'))
            ->assertSee('Абдуллаев Бахтиёр Рустамович')
            ->assertSee('Макроэкономическое прогнозирование · Бюджетная политика')
            ->assertSee(route('research-staff.show', 'bakhtiyor-abdullaev'), false)
            ->assertSee('rs-index-list', false)
            ->assertSee('rs-search', false);

        // Only the first sector pane is visible before Alpine runs; the rest are cloaked.
        expect(substr_count($response->getContent(), 'role="tabpanel"'))->toBe(count(ResearchStaffPrototype::SECTORS))
            ->and(substr_count($response->getContent(), 'role="tabpanel" style="--rs-accent: #1b69c4" data-sector="macroeconomic-policy"'))->toBe(1)
            ->and(preg_match_all('/data-sector="[^"]+"\s+x-show="[^"]+"\s+x-cloak/', $response->getContent()))->toBe(count(ResearchStaffPrototype::SECTORS) - 1);
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
            ->and(ResearchStaffPrototype::sectorName('labour'))->not->toContain('staff.');
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
