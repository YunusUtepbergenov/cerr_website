<?php

use App\Support\RegionShapes;
use App\Support\ResearchStaffPrototype;

describe('Research staff prototype pages', function () {
    it('lists every sector and branch as a card linking to its own page', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.index'))->assertOk();

        foreach (ResearchStaffPrototype::SECTORS as $sector) {
            $response->assertSee(ResearchStaffPrototype::sectorName($sector['key']))
                ->assertSee(route('research-staff.sector', $sector['slug']), false);
        }

        $response->assertSee(__('staff.title'))
            ->assertSee(__('staff.see_more'))
            ->assertSeeInOrder(['rs-hero', 'article-breadcrumb', 'rs-hero-text', 'rs-hero-photo', 'images/research-staff/cerr-building.webp', 'rs-sector-grid'], false)
            ->assertSee('<h1 class="rs-title">'.__('staff.eyebrow').'</h1>', false)
            ->assertDontSee('rs-ring', false)
            ->assertDontSee('rs-hero-stat', false)
            ->assertDontSee('rs-lead', false)
            ->assertDontSee('rs-eyebrow', false)
            ->assertDontSee('rs-note', false)
            ->assertDontSee('rs-stats', false)
            ->assertDontSee('rs-sector-num', false)
            ->assertSee('data-icon="trend"', false)
            ->assertSee('data-icon="map:uzbekistan"', false)
            ->assertSee('data-icon="map:jizzax"', false)
            ->assertSee('rs-icon--map', false)
            ->assertSee('rs-stack-item', false)
            ->assertSee(__('staff.sector_short_macro'))
            ->assertSee(__('staff.sector_desc_macro'))
            ->assertSee('Насриддинов Фазлиддин Файзуллаевич')
            ->assertSee('Бегманов Саламат Сарсенбаевич')
            ->assertSee(__('staff.head_of_sector'))
            ->assertDontSee('rs-side', false)
            ->assertDontSee(route('research-staff.show', 'fazliddin-nasriddinov'), false);

        expect(substr_count($response->getContent(), 'data-sector-card="'))->toBe(count(ResearchStaffPrototype::SECTORS));
    })->group('feature', 'routes');

    it('shows a sector page with its head first, then staff, and the sector list', function () {
        setAppLocale('ru');

        $response = $this->get(route('research-staff.sector', 'macroeconomic-policy'))->assertOk();

        $response->assertSee(__('structure.macro'))
            ->assertSee(__('staff.staff_of_sector'))
            ->assertSee(__('staff.head_of_sector'))
            ->assertSeeInOrder(['Насриддинов Фазлиддин Файзуллаевич', 'Ахматов Жамшид Неъматилла ўғли', 'Сапарматов Исломбек Икрамбек угли'])
            ->assertSeeInOrder([__('staff.position_head'), __('staff.position_chief'), __('staff.position_leading')])
            ->assertSee(route('research-staff.show', 'fazliddin-nasriddinov'), false)
            ->assertSee('mailto:f.nasriddinov@cerr.uz', false)
            ->assertSee(route('research-staff.sector', 'regional-development'), false)
            ->assertSee('data-icon="trend"', false)
            ->assertDontSee('04 / 14')
            ->assertDontSee('rs-note', false)
            ->assertSeeInOrder(['rs-hero-text--icon', 'data-icon="trend"', 'rs-title--sector', __('structure.macro'), 'rs-hero-photo', 'rs-people', 'Насриддинов Фазлиддин Файзуллаевич'], false)
            ->assertDontSee('rs-hero-stat', false)
            ->assertDontSee('rs-hero-head', false)
            ->assertDontSee('rs-lead', false)
            ->assertDontSee('rs-eyebrow', false)
            ->assertDontSee('rs-ring', false);

        expect(substr_count($response->getContent(), 'data-person="'))->toBe(3);
    })->group('feature', 'routes');

    it('shows a regional branch with its staff and e-mails from the staff list', function () {
        setAppLocale('uz');

        $this->get(route('research-staff.sector', 'karakalpakstan-branch'))
            ->assertOk()
            ->assertSee(__('structure.karakalpakistan'))
            ->assertSee('Begmanov Salamat Sarsenbayevich')
            ->assertSee('mailto:s.beqmanov@cerr.uz', false)
            ->assertSee('data-icon="map:karakalpakistan"', false)
            ->assertSee('d="'.RegionShapes::PATHS['karakalpakistan'].'"', false)
            ->assertDontSee('10 / 14');
    })->group('feature', 'routes');

    it('calls a sector шўъба / sho‘ba and never abbreviates the Centre as ИТИМ in both Uzbek scripts', function () {
        setAppLocale('kr');
        $this->get(route('research-staff.sector', 'macroeconomic-policy'))
            ->assertOk()
            ->assertSee('Шўъба мудири')
            ->assertSee('Шўъба ходимлари')
            ->assertDontSee('Сектор ')
            ->assertDontSee('>Сектор<', false);
        $this->get(route('research-staff.index'))
            ->assertOk()
            ->assertSee(__('staff.eyebrow'))
            ->assertDontSee('ИТИМ');

        setAppLocale('uz');
        $this->get(route('research-staff.index'))
            ->assertOk()
            ->assertSee('Sho‘ba mudiri')
            ->assertSee(__('staff.eyebrow'))
            ->assertDontSee('Sektor')
            ->assertDontSee('ITIM');
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

    it('shows a profile from the staff list and says the questionnaire is pending', function () {
        setAppLocale('en');

        $this->get(route('research-staff.show', 'fazliddin-nasriddinov'))
            ->assertOk()
            ->assertSee('Fazliddin Nasriddinov')
            ->assertSee(__('staff.position_head'))
            ->assertSee('mailto:f.nasriddinov@cerr.uz', false)
            ->assertSee(__('structure.macro'))
            ->assertSee(__('staff.profile_pending'))
            ->assertDontSee('rs-note', false)
            ->assertSeeInOrder([__('staff.part_education'), __('staff.part_research'), __('staff.part_publications')])
            ->assertDontSee('rs-profiles', false)
            ->assertDontSee('rs-side-nav', false)
            ->assertDontSee(__('staff.bachelor'))
            ->assertSee(__('staff.colleagues'))
            ->assertSee('Jamshid Akhmatov')
            ->assertSee(route('research-staff.sector', 'macroeconomic-policy'), false)
            ->assertSee(route('research-staff.index'), false)
            ->assertSee('<meta name="description" content="'.__('staff.position_head').', '.__('structure.macro').'.">', false);
    })->group('feature', 'routes');

    it('shows names in the Latin Uzbek locale from the uz key, not the English one', function () {
        setAppLocale('uz');

        $this->get(route('research-staff.show', 'dostonjon-kuldoshov'))
            ->assertOk()
            ->assertSee('Qo‘ldoshov Dostonjon G‘ani o‘g‘li')
            ->assertDontSee('>Dostonjon Kuldoshov<', false);
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
            ->and(ResearchStaffPrototype::text(['ru' => 'Р', 'uz' => 'U', 'en' => 'E'], 'uz'))->toBe('U')
            ->and(ResearchStaffPrototype::text(['ru' => 'Р'], 'en'))->toBe('Р')
            ->and(ResearchStaffPrototype::text([], 'ru'))->toBe('')
            ->and(ResearchStaffPrototype::text('As is', 'kr'))->toBe('As is')
            ->and(ResearchStaffPrototype::items(['ru' => ['а', 'б'], 'en' => ['a']], 'uz'))->toBe(['a'])
            ->and(ResearchStaffPrototype::items(['ru' => ['а', 'б']], 'en'))->toBe(['а', 'б'])
            ->and(ResearchStaffPrototype::initials('Насриддинов Фазлиддин Файзуллаевич'))->toBe('НФ')
            ->and(ResearchStaffPrototype::initials('Fazliddin Nasriddinov'))->toBe('FN');
    })->group('unit');

    it('names every unit from the structure strings and finds sectors by slug', function () {
        setAppLocale('ru');

        expect(ResearchStaffPrototype::SECTORS)->toHaveCount(14)
            ->and(collect(ResearchStaffPrototype::SECTORS)->every(fn (array $sector) => ResearchStaffPrototype::sectorName($sector['key']) === __('structure.'.$sector['key'])))->toBeTrue()
            ->and(ResearchStaffPrototype::sectorName('macro'))->not->toContain('structure.')
            ->and(ResearchStaffPrototype::findSector('macroeconomic-policy')['head']['slug'])->toBe('fazliddin-nasriddinov')
            ->and(ResearchStaffPrototype::findSector('nope'))->toBeNull();
    })->group('unit');

    it('has an outline for every map icon and keeps the shapes small', function () {
        $mapIcons = collect(ResearchStaffPrototype::SECTORS)->pluck('icon')->filter(fn (string $icon) => str_starts_with($icon, 'map:'))->map(fn (string $icon) => substr($icon, 4))->values();

        expect($mapIcons->all())->toBe(['uzbekistan', 'karakalpakistan', 'jizzax', 'namangan', 'samarkand', 'kashkadarya'])
            ->and($mapIcons->every(fn (string $key) => RegionShapes::path($key) !== null))->toBeTrue()
            ->and(RegionShapes::path('atlantis'))->toBeNull()
            ->and(collect(RegionShapes::PATHS)->every(fn (string $d) => str_starts_with($d, 'M') && str_ends_with($d, 'Z') && strlen($d) < 1200))->toBeTrue();
    })->group('unit');

    it('carries the whole staff list with unique slugs and e-mails and the questionnaire fields', function () {
        $people = ResearchStaffPrototype::people();
        $sectors = ResearchStaffPrototype::sectors();
        $fields = [...ResearchStaffPrototype::EDUCATION_FIELDS, ...ResearchStaffPrototype::ACTIVITY_FIELDS, ...ResearchStaffPrototype::PUBLICATION_FIELDS, 'research_areas', 'profiles'];

        expect($people)->toHaveCount(53)
            ->and($people->pluck('slug')->unique())->toHaveCount($people->count())
            ->and($people->pluck('email')->unique())->toHaveCount($people->count())
            ->and($people->every(fn (array $person) => str_ends_with($person['email'], '@cerr.uz')))->toBeTrue()
            ->and($people->every(fn (array $person) => isset($person['name']['kr'], $person['name']['ru'], $person['name']['uz'], $person['name']['en'])))->toBeTrue()
            ->and($people->every(fn (array $person) => count(array_diff($fields, array_keys($person))) === 0))->toBeTrue()
            ->and($people->sum(fn (array $person) => ResearchStaffPrototype::publicationCount($person)))->toBe(0)
            ->and($sectors->sum(fn ($sector) => $sector['staff']->count() + ($sector['head'] ? 1 : 0)))->toBe(53)
            ->and($sectors->every(fn ($sector) => $sector['head'] !== null))->toBeTrue()
            ->and($sectors->firstWhere('key', 'karakalpakistan')['staff'])->toHaveCount(2);
    })->group('unit');
});
