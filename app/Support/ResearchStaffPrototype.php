<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Sample data behind the research-staff prototype pages.
 *
 * The shape of a person mirrors the staff questionnaire (анкета сотрудника
 * для официального сайта), section by section:
 *
 *   I.   Education and qualification — name, position, bachelor, master,
 *        doctorate, academic degree and title.
 *   II.  Research and expert activity — research areas (≤5), international
 *        activity, professional achievements, previous experience (≤3).
 *   III. Publications and research — articles (5–10), monographs and books,
 *        analytical reports, scientific profiles (ORCID, Google Scholar, Scopus).
 *
 * The questionnaire is filled in Uzbek (Cyrillic), Russian and English, so
 * localised values carry `kr`, `ru` and `en` keys (names also carry `uz`);
 * Latin Uzbek falls back through {@see self::text()}.
 *
 * Sectors, names, positions and e-mails come from the centre's staff list
 * (xodimlar.xlsx, October 2026). The questionnaire sections stay empty until
 * each person's анкета arrives; real models and an admin form come next.
 *
 * @phpstan-type Localised array<string, string>
 * @phpstan-type Entry array{years: string, text: Localised}
 * @phpstan-type Publication array{text: Localised|string, url?: string}
 * @phpstan-type Person array{
 *     slug: string, sector: string, is_head: bool, position: string, degree: ?string,
 *     email: string, phone: ?string, photo: ?string,
 *     name: Localised, bachelor: Localised, master: Localised, doctorate: Localised, degree_title: Localised,
 *     research_areas: array<string, list<string>>, international: list<Entry>, achievements: list<Entry>, previous_experience: list<Entry>,
 *     articles: list<Publication>, books: list<Publication>, reports: list<Publication>,
 *     profiles: array{orcid: ?string, scholar: ?string, scopus: ?string}
 * }
 */
class ResearchStaffPrototype
{
    /**
     * Sectors, branches and the editorial office in staff-list order. `icon` is a
     * name known to <x-research-staff.sector-icon>; `map:{region}` draws the
     * outline from {@see RegionShapes}.
     *
     * @var list<array{key: string, slug: string, accent: string, icon: string}>
     */
    public const SECTORS = [
        ['key' => 'competitiveness', 'slug' => 'competitiveness-and-investment', 'accent' => '#b45309', 'icon' => 'factory'],
        ['key' => 'regions', 'slug' => 'regional-development', 'accent' => '#15803d', 'icon' => 'map:uzbekistan'],
        ['key' => 'cluster', 'slug' => 'industrial-clusters', 'accent' => '#be123c', 'icon' => 'cluster'],
        ['key' => 'macro', 'slug' => 'macroeconomic-policy', 'accent' => '#1b69c4', 'icon' => 'trend'],
        ['key' => 'bank', 'slug' => 'banking-and-capital-markets', 'accent' => '#0f766e', 'icon' => 'bank'],
        ['key' => 'foreign_economic', 'slug' => 'foreign-economic-activity', 'accent' => '#6d28d9', 'icon' => 'globe'],
        ['key' => 'marketing', 'slug' => 'public-relations-and-marketing', 'accent' => '#c2410c', 'icon' => 'megaphone'],
        ['key' => 'information_tech', 'slug' => 'it-and-databases', 'accent' => '#0369a1', 'icon' => 'database'],
        ['key' => 'accelerator', 'slug' => 'reform-accelerator', 'accent' => '#7c3aed', 'icon' => 'rocket'],
        ['key' => 'magazine', 'slug' => 'economic-review-editorial', 'accent' => '#9f1239', 'icon' => 'journal'],
        ['key' => 'karakalpakistan', 'slug' => 'karakalpakstan-branch', 'accent' => '#0e7490', 'icon' => 'map:karakalpakistan'],
        ['key' => 'jizzax', 'slug' => 'jizzakh-branch', 'accent' => '#4d7c0f', 'icon' => 'map:jizzax'],
        ['key' => 'namangan', 'slug' => 'namangan-branch', 'accent' => '#a16207', 'icon' => 'map:namangan'],
        ['key' => 'samarkand', 'slug' => 'samarkand-branch', 'accent' => '#1d4ed8', 'icon' => 'map:samarkand'],
        ['key' => 'kashkadarya', 'slug' => 'kashkadarya-branch', 'accent' => '#be185d', 'icon' => 'map:kashkadarya'],
    ];

    /** Questionnaire part I, in form order; each key holds a localised text. */
    public const EDUCATION_FIELDS = ['bachelor', 'master', 'doctorate', 'degree_title'];

    /** Questionnaire part II lists with years, in form order. */
    public const ACTIVITY_FIELDS = ['international', 'achievements', 'previous_experience'];

    /** Questionnaire part III publication lists, in form order. */
    public const PUBLICATION_FIELDS = ['articles', 'books', 'reports'];

    /**
     * Research sectors in display order, each with its people attached.
     *
     * @return Collection<int, array{key: string, slug: string, accent: string, icon: string, head: ?Person, staff: Collection<int, Person>}>
     */
    public static function sectors(): Collection
    {
        $people = self::people()->groupBy('sector');

        return collect(self::SECTORS)->map(function (array $sector) use ($people) {
            $members = $people->get($sector['key'], collect());

            return $sector + [
                'head' => $members->firstWhere('is_head', true),
                'staff' => $members->reject(fn (array $person) => $person['is_head'])->values(),
            ];
        });
    }

    /** @return Collection<int, Person> */
    public static function people(): Collection
    {
        return collect(self::DATA)->map(fn (array $row) => self::normalise($row));
    }

    /** @return ?Person */
    public static function find(string $slug): ?array
    {
        return self::people()->firstWhere('slug', $slug);
    }

    /** @return ?array{key: string, slug: string, accent: string, icon: string} */
    public static function sector(string $key): ?array
    {
        return collect(self::SECTORS)->firstWhere('key', $key);
    }

    /**
     * A sector by its URL slug, with its head and staff attached.
     *
     * @return ?array{key: string, slug: string, accent: string, icon: string, head: ?Person, staff: Collection<int, Person>}
     */
    public static function findSector(string $slug): ?array
    {
        return self::sectors()->firstWhere('slug', $slug);
    }

    /**
     * Full sector name: the centre's structure strings where they exist,
     * otherwise the prototype's own placeholder names.
     */
    public static function sectorName(string $key): string
    {
        return Lang::has('structure.'.$key) ? __('structure.'.$key) : __('staff.sector_name_'.$key);
    }

    /**
     * Pick the value for the current locale with sensible fallbacks.
     *
     * Plain strings (e.g. a publication kept in its original language) are
     * returned as they are.
     *
     * @param  Localised|string|null  $values
     */
    public static function text(array|string|null $values, ?string $locale = null): string
    {
        if (is_string($values)) {
            return $values;
        }

        $locale ??= app()->getLocale();
        $chain = match ($locale) {
            'kr' => ['kr', 'ru', 'uz', 'en'],
            'uz' => ['uz', 'en', 'kr', 'ru'],
            'en' => ['en', 'uz', 'ru', 'kr'],
            default => ['ru', 'kr', 'en', 'uz'],
        };

        foreach ($chain as $candidate) {
            if (($values[$candidate] ?? '') !== '') {
                return $values[$candidate];
            }
        }

        return '';
    }

    /**
     * Pick a localised list (research areas) with the same fallback order.
     *
     * @param  array<string, list<string>>  $values
     * @return list<string>
     */
    public static function items(array $values, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $chain = match ($locale) {
            'kr' => ['kr', 'ru', 'uz', 'en'],
            'uz' => ['uz', 'en', 'kr', 'ru'],
            'en' => ['en', 'uz', 'ru', 'kr'],
            default => ['ru', 'kr', 'en', 'uz'],
        };

        foreach ($chain as $candidate) {
            if (! empty($values[$candidate])) {
                return array_values($values[$candidate]);
            }
        }

        return [];
    }

    /**
     * Two-letter monogram for people without a photo.
     */
    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = array_map(fn (string $part) => mb_substr($part, 0, 1), array_slice($parts, 0, 2));

        return mb_strtoupper(implode('', $letters));
    }

    /**
     * Number of publications across articles, books and reports.
     *
     * @param  Person  $person
     */
    public static function publicationCount(array $person): int
    {
        return collect(self::PUBLICATION_FIELDS)->sum(fn (string $field) => count($person[$field]));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return Person
     */
    private static function normalise(array $row): array
    {
        $row += [
            'is_head' => false,
            'degree' => null,
            'phone' => null,
            'photo' => null,
            'bachelor' => [],
            'master' => [],
            'doctorate' => [],
            'degree_title' => [],
            'research_areas' => [],
            'international' => [],
            'achievements' => [],
            'previous_experience' => [],
            'articles' => [],
            'books' => [],
            'reports' => [],
            'profiles' => [],
        ];
        $row['profiles'] += ['orcid' => null, 'scholar' => null, 'scopus' => null];
        $row['slug'] ??= Str::slug($row['name']['en']);
        $row['email'] ??= Str::lower(Str::of($row['name']['en'])->explode(' ')->map(fn ($p, $i) => $i === 0 ? mb_substr($p, 0, 1) : $p)->implode('.')).'@cerr.uz';

        return $row;
    }

    /** @var list<array<string, mixed>> */
    private const DATA = [
        // ---- Competitiveness and investment ----------------------------------------
        [
            'sector' => 'competitiveness', 'is_head' => true, 'position' => 'head', 'email' => 'b.panjiev@cerr.uz',
            'name' => ['kr' => 'Панжиев Бекзод Равшан ўғли', 'ru' => 'Панжиев Бекзод Равшан ўғли', 'uz' => 'Panjiyev Bekzod Ravshan o‘g‘li', 'en' => 'Bekzod Panjiev'],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'chief', 'email' => 'r.abdullaev@cerr.uz',
            'name' => ['kr' => 'Абдуллаев Руслан Раманович', 'ru' => 'Абдуллаев Руслан Раманович', 'uz' => 'Abdullayev Ruslan Ramanovich', 'en' => 'Ruslan Abdullaev'],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'chief', 'email' => 'j.usmonov@cerr.uz',
            'name' => ['kr' => 'Усмонов Жалолиддин Шухрат ўғли', 'ru' => 'Усмонов Жалолиддин Шухрат ўғли', 'uz' => 'Usmonov Jaloliddin Shuxrat o‘g‘li', 'en' => 'Jaloliddin Usmonov'],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'leading', 'email' => 'z.goyibov@cerr.uz',
            'name' => ['kr' => 'Ғойибов Зохиджон Араббоевич', 'ru' => 'Ғойибов Зохиджон Араббоевич', 'uz' => 'G‘oyibov Zoxijon Arabboyevich', 'en' => 'Zokhijon Goyibov'],
        ],
        // ---- Regional development --------------------------------------------------
        [
            'sector' => 'regions', 'is_head' => true, 'position' => 'head', 'email' => 'x.jurayev@cerr.uz',
            'name' => ['kr' => 'Жураев Хуршид Маматкулович', 'ru' => 'Жураев Хуршид Маматкулович', 'uz' => 'Jurayev Xurshid Mamatkulovich', 'en' => 'Khurshid Juraev'],
        ],
        [
            'sector' => 'regions', 'position' => 'chief', 'email' => 'b.ismailov@cerr.uz',
            'name' => ['kr' => 'Исмаилов Бахтиёр Абдухамид ўғли', 'ru' => 'Исмаилов Бахтиёр Абдухамид ўғли', 'uz' => 'Ismailov Baxtiyor Abduxamid o‘g‘li', 'en' => 'Bakhtiyor Ismailov'],
        ],
        [
            'sector' => 'regions', 'position' => 'chief', 'email' => 'y.fayzullaev@cerr.uz',
            'name' => ['kr' => 'Файзуллаев Ядгар Шукуруллаевич', 'ru' => 'Файзуллаев Ядгар Шукуруллаевич', 'uz' => 'Fayzullayev Yadgar Shukurullayevich', 'en' => 'Yadgar Fayzullaev'],
        ],
        [
            'sector' => 'regions', 'position' => 'leading', 'email' => 'z.kosimova@cerr.uz',
            'name' => ['kr' => 'Косимова Зарина Алижон қизи', 'ru' => 'Косимова Зарина Алижон қизи', 'uz' => 'Kosimova Zarina Alijon qizi', 'en' => 'Zarina Kosimova'],
        ],
        [
            'sector' => 'regions', 'position' => 'leading', 'email' => 'sh.honkulov@cerr.uz',
            'name' => ['kr' => 'Хонкулов Шухрат Холиқул ўғли', 'ru' => 'Хонкулов Шухрат Холиқул ўғли', 'uz' => 'Xonkulov Shuxrat Xoliqul o‘g‘li', 'en' => 'Shukhrat Khonkulov'],
        ],
        [
            'sector' => 'regions', 'position' => 'leading', 'email' => 'i.abdujabbarov@cerr.uz',
            'name' => ['kr' => 'Абдужаббаров Икром Абдуғани ўғли', 'ru' => 'Абдужаббаров Икром Абдуғани ўғли', 'uz' => 'Abdujabbarov Ikrom Abdug‘ani o‘g‘li', 'en' => 'Ikrom Abdujabbarov'],
        ],
        // ---- Industrial clusters ---------------------------------------------------
        [
            'sector' => 'cluster', 'is_head' => true, 'position' => 'head', 'email' => 'r.abaturov@cerr.uz',
            'name' => ['kr' => 'Абатуров Руслан Викторович', 'ru' => 'Абатуров Руслан Викторович', 'uz' => 'Abaturov Ruslan Viktorovich', 'en' => 'Ruslan Abaturov'],
        ],
        [
            'sector' => 'cluster', 'position' => 'chief', 'email' => 'j.adizov@cerr.uz',
            'name' => ['kr' => 'Адизов Жахонгир Гайратович', 'ru' => 'Адизов Жахонгир Гайратович', 'uz' => 'Adizov Jaxongir Gayratovich', 'en' => 'Jakhongir Adizov'],
        ],
        [
            'sector' => 'cluster', 'position' => 'leading', 'email' => 'm.mirxoshimova@cerr.uz',
            'name' => ['kr' => 'Мирхошимова Мохирахон Ботир кизи', 'ru' => 'Мирхошимова Мохирахон Ботир кизи', 'uz' => 'Mirxoshimova Moxiraxon Botir qizi', 'en' => 'Mokhirakhon Mirkhoshimova'],
        ],
        // ---- Macroeconomic policy --------------------------------------------------
        [
            'sector' => 'macro', 'is_head' => true, 'position' => 'head', 'email' => 'f.nasriddinov@cerr.uz',
            'name' => ['kr' => 'Насриддинов Фазлиддин Файзуллаевич', 'ru' => 'Насриддинов Фазлиддин Файзуллаевич', 'uz' => 'Nasriddinov Fazliddin Fayzullayevich', 'en' => 'Fazliddin Nasriddinov'],
        ],
        [
            'sector' => 'macro', 'position' => 'chief', 'email' => 'j.axmatov@cerr.uz',
            'name' => ['kr' => 'Ахматов Жамшид Неъматилла ўғли', 'ru' => 'Ахматов Жамшид Неъматилла ўғли', 'uz' => 'Axmatov Jamshid Ne’matilla o‘g‘li', 'en' => 'Jamshid Akhmatov'],
        ],
        [
            'sector' => 'macro', 'position' => 'leading', 'email' => 'i.saparmatov@cerr.uz',
            'name' => ['kr' => 'Сапарматов Исломбек Икрамбек угли', 'ru' => 'Сапарматов Исломбек Икрамбек угли', 'uz' => 'Saparmatov Islombek Ikrambek o‘g‘li', 'en' => 'Islombek Saparmatov'],
        ],
        // ---- Banking and capital markets -------------------------------------------
        [
            'sector' => 'bank', 'is_head' => true, 'position' => 'head', 'email' => 'j.xidirov@cerr.uz',
            'name' => ['kr' => 'Хидиров Жаъфар Жабборович', 'ru' => 'Хидиров Жаъфар Жабборович', 'uz' => 'Xidirov Ja’far Jabborovich', 'en' => 'Jafar Khidirov'],
        ],
        [
            'sector' => 'bank', 'position' => 'chief', 'email' => 'i.khudayberganov@cerr.uz',
            'name' => ['kr' => 'Худайберганов Ислом Неъматуллаевич', 'ru' => 'Худайберганов Ислом Неъматуллаевич', 'uz' => 'Xudayberganov Islom Ne’matullayevich', 'en' => 'Islom Khudayberganov'],
        ],
        [
            'sector' => 'bank', 'position' => 'chief', 'email' => 'd.qo\'ldoshov@cerr.uz',
            'name' => ['kr' => 'Қўлдошов Достонжон Ғани ўғли', 'ru' => 'Қўлдошов Достонжон Ғани ўғли', 'uz' => 'Qo‘ldoshov Dostonjon G‘ani o‘g‘li', 'en' => 'Dostonjon Kuldoshov'],
        ],
        [
            'sector' => 'bank', 'position' => 'leading', 'email' => 'm.arifdjanova@cerr.uz',
            'name' => ['kr' => 'Арифджанова Мубинахон Анваровна', 'ru' => 'Арифджанова Мубинахон Анваровна', 'uz' => 'Arifjanova Mubinaxon Anvarovna', 'en' => 'Mubinakhon Arifjanova'],
        ],
        // ---- Foreign economic activity ---------------------------------------------
        [
            'sector' => 'foreign_economic', 'is_head' => true, 'position' => 'head', 'email' => 'a.bozorov@cerr.uz',
            'name' => ['kr' => 'Бозоров Абдурашид Тойир ўғли', 'ru' => 'Бозоров Абдурашид Тойир ўғли', 'uz' => 'Bozorov Abdurashid Toyir o‘g‘li', 'en' => 'Abdurashid Bozorov'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'chief', 'email' => 'y.kutbitdinov@cerr.uz',
            'name' => ['kr' => 'Кутбитдинов Юрий Шахабуддинович', 'ru' => 'Кутбитдинов Юрий Шахабуддинович', 'uz' => 'Kutbitdinov Yuriy Shaxabuddinovich', 'en' => 'Yuriy Kutbitdinov'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'chief', 'email' => 'a.artikov@cerr.uz',
            'name' => ['kr' => 'Артиков Афзал Аскарович', 'ru' => 'Артиков Афзал Аскарович', 'uz' => 'Artikov Afzal Askarovich', 'en' => 'Afzal Artikov'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'leading', 'email' => 'k.kuncheraeva@cerr.uz',
            'name' => ['kr' => 'Кунчераева Камила Илесбаевна', 'ru' => 'Кунчераева Камила Илесбаевна', 'uz' => 'Kuncherayeva Kamila Ilesbayevna', 'en' => 'Kamila Kuncheraeva'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'leading', 'email' => 'k.fazliddinov@cerr.uz',
            'name' => ['kr' => 'Фазлиддинов Камолиддин Фатхиддин угли', 'ru' => 'Фазлиддинов Камолиддин Фатхиддин угли', 'uz' => 'Fazliddinov Kamoliddin Fatxiddin o‘g‘li', 'en' => 'Kamoliddin Fazliddinov'],
        ],
        // ---- Public relations and marketing ----------------------------------------
        [
            'sector' => 'marketing', 'is_head' => true, 'position' => 'head', 'email' => 'z.rizaeva@cerr.uz',
            'name' => ['kr' => 'Ризаева Зиёда Арзуевна', 'ru' => 'Ризаева Зиёда Арзуевна', 'uz' => 'Rizayeva Ziyoda Arzuyevna', 'en' => 'Ziyoda Rizaeva'],
        ],
        [
            'sector' => 'marketing', 'position' => 'chief', 'email' => 'o.olimjonov@cerr.uz',
            'name' => ['kr' => 'Олимжонов Одил Олимович', 'ru' => 'Олимжонов Одил Олимович', 'uz' => 'Olimjonov Odil Olimovich', 'en' => 'Odil Olimjonov'],
        ],
        [
            'sector' => 'marketing', 'position' => 'chief', 'email' => 'e.romanov@cerr.uz',
            'name' => ['kr' => 'Романов Эдвард Юревич', 'ru' => 'Романов Эдвард Юревич', 'uz' => 'Romanov Edvard Yurevich', 'en' => 'Edvard Romanov'],
        ],
        [
            'sector' => 'marketing', 'position' => 'leading', 'email' => 'l.buriyeva@cerr.uz',
            'name' => ['kr' => 'Бўриева Латофат Абдуалимовна', 'ru' => 'Бўриева Латофат Абдуалимовна', 'uz' => 'Bo‘riyeva Latofat Abdualimovna', 'en' => 'Latofat Burieva'],
        ],
        [
            'sector' => 'marketing', 'position' => 'leading', 'email' => 'a.abduqaxxorov@cerr.uz',
            'name' => ['kr' => 'Абдуқаххоров Азизбек Абдулло ўғли', 'ru' => 'Абдуқаххоров Азизбек Абдулло ўғли', 'uz' => 'Abduqaxxorov Azizbek Abdullo o‘g‘li', 'en' => 'Azizbek Abdukakhorov'],
        ],
        [
            'sector' => 'marketing', 'position' => 'leading', 'email' => 'j.turdimov@cerr.uz',
            'name' => ['kr' => 'Турдимов Жамолиддин Бекмурзаевич', 'ru' => 'Турдимов Жамолиддин Бекмурзаевич', 'uz' => 'Turdimov Jamoliddin Bekmurzayevich', 'en' => 'Jamoliddin Turdimov'],
        ],
        // ---- IT and databases ------------------------------------------------------
        [
            'sector' => 'information_tech', 'is_head' => true, 'position' => 'head', 'email' => 'a.gaibullayev@cerr.uz',
            'name' => ['kr' => 'Гайбуллаев Абдулазиз Ғофиржон ўғли', 'ru' => 'Гайбуллаев Абдулазиз Ғофиржон ўғли', 'uz' => 'Gaybullayev Abdulaziz G‘ofirjon o‘g‘li', 'en' => 'Abdulaziz Gaybullaev'],
        ],
        [
            'sector' => 'information_tech', 'position' => 'chief', 'email' => 'i.rabbimov@cerr.uz',
            'name' => ['kr' => 'Раббимов Илёс Меҳриддинович', 'ru' => 'Раббимов Илёс Меҳриддинович', 'uz' => 'Rabbimov Ilyos Mehriddinovich', 'en' => 'Ilyos Rabbimov'],
        ],
        [
            'sector' => 'information_tech', 'position' => 'chief', 'email' => 'f.tutkabaev@cerr.uz',
            'name' => ['kr' => 'Туткабаев Фархад Кайратович', 'ru' => 'Туткабаев Фархад Кайратович', 'uz' => 'Tutkabayev Farxad Kayratovich', 'en' => 'Farkhad Tutkabaev'],
        ],
        [
            'sector' => 'information_tech', 'position' => 'leading', 'email' => 'y.utepbergenov@cerr.uz',
            'name' => ['kr' => 'Утепбергенов Юнус Қутлимуратович', 'ru' => 'Утепбергенов Юнус Қутлимуратович', 'uz' => 'Utepbergenov Yunus Qutlimuratovich', 'en' => 'Yunus Utepbergenov'],
        ],
        // ---- Reform accelerator ----------------------------------------------------
        [
            'sector' => 'accelerator', 'is_head' => true, 'position' => 'head', 'email' => 'a.halmurzaev@cerr.uz',
            'name' => ['kr' => 'Халмурзаев Алишер Абдукаимжанович', 'ru' => 'Халмурзаев Алишер Абдукаимжанович', 'uz' => 'Xalmurzayev Alisher Abdukaimjanovich', 'en' => 'Alisher Khalmurzaev'],
        ],
        [
            'sector' => 'accelerator', 'position' => 'chief', 'email' => 'm.maxmudov@cerr.uz',
            'name' => ['kr' => 'Махмудов Маъруф Мухтор угли', 'ru' => 'Махмудов Маъруф Мухтор угли', 'uz' => 'Maxmudov Ma’ruf Muxtor o‘g‘li', 'en' => 'Maruf Makhmudov'],
        ],
        // ---- Economic Review editorial office --------------------------------------
        [
            'sector' => 'magazine', 'position' => 'web_editor', 'email' => 'm.yusupov@cerr.uz',
            'name' => ['kr' => 'Юсупов Маркс Бахтиёрович', 'ru' => 'Юсупов Маркс Бахтиёрович', 'uz' => 'Yusupov Marks Baxtiyorovich', 'en' => 'Marks Yusupov'],
        ],
        [
            'sector' => 'magazine', 'position' => 'senior_editor', 'email' => 'v.abaturov@cerr.uz',
            'name' => ['kr' => 'Абатуров Виктор Борисович', 'ru' => 'Абатуров Виктор Борисович', 'uz' => 'Abaturov Viktor Borisovich', 'en' => 'Viktor Abaturov'],
        ],
        // ---- Karakalpakstan branch -------------------------------------------------
        [
            'sector' => 'karakalpakistan', 'is_head' => true, 'position' => 'head', 'email' => 's.beqmanov@cerr.uz',
            'name' => ['kr' => 'Бегманов Саламат Сарсенбаевич', 'ru' => 'Бегманов Саламат Сарсенбаевич', 'uz' => 'Begmanov Salamat Sarsenbayevich', 'en' => 'Salamat Begmanov'],
        ],
        [
            'sector' => 'karakalpakistan', 'position' => 'chief', 'email' => 's.ilyasov@cerr.uz',
            'name' => ['kr' => 'Ильясов Салый Пухарбаевич', 'ru' => 'Ильясов Салый Пухарбаевич', 'uz' => 'Ilyasov Saliy Puxarbayevich', 'en' => 'Saliy Ilyasov'],
        ],
        [
            'sector' => 'karakalpakistan', 'position' => 'leading', 'email' => 'y.jumabaev@cerr.uz',
            'name' => ['kr' => 'Жумабаев Юсуп Жолдасбаевич', 'ru' => 'Жумабаев Юсуп Жолдасбаевич', 'uz' => 'Jumabayev Yusup Joldasbayevich', 'en' => 'Yusup Jumabaev'],
        ],
        // ---- Jizzakh branch --------------------------------------------------------
        [
            'sector' => 'jizzax', 'is_head' => true, 'position' => 'head', 'email' => 'f.rakhimov@cerr.uz',
            'name' => ['kr' => 'Рахимов Феруз Уйғунович', 'ru' => 'Рахимов Феруз Уйғунович', 'uz' => 'Raximov Feruz Uyg‘unovich', 'en' => 'Feruz Rakhimov'],
        ],
        [
            'sector' => 'jizzax', 'position' => 'chief', 'email' => 'e.pulatov@cerr.uz',
            'name' => ['kr' => 'Пулатов Элёр Султанович', 'ru' => 'Пулатов Элёр Султанович', 'uz' => 'Pulatov Elyor Sultanovich', 'en' => 'Elyor Pulatov'],
        ],
        [
            'sector' => 'jizzax', 'position' => 'leading', 'email' => 'f.saibov@cerr.uz',
            'name' => ['kr' => 'Саибов Фаррух Фарходович', 'ru' => 'Саибов Фаррух Фарходович', 'uz' => 'Saibov Farrux Farxodovich', 'en' => 'Farrukh Saibov'],
        ],
        // ---- Namangan branch -------------------------------------------------------
        [
            'sector' => 'namangan', 'is_head' => true, 'position' => 'head', 'email' => 'm.abdusamatov@cerr.uz',
            'name' => ['kr' => 'Абдусаматов Музаффар Джўраевич', 'ru' => 'Абдусаматов Музаффар Джўраевич', 'uz' => 'Abdusamatov Muzaffar Jo‘rayevich', 'en' => 'Muzaffar Abdusamatov'],
        ],
        [
            'sector' => 'namangan', 'position' => 'chief', 'email' => 'b.xolmirzayev@cerr.uz',
            'name' => ['kr' => 'Холмирзаев Баходир Бахрамжон ўғли', 'ru' => 'Холмирзаев Баходир Бахрамжон ўғли', 'uz' => 'Xolmirzayev Baxodir Baxramjon o‘g‘li', 'en' => 'Bakhodir Kholmirzaev'],
        ],
        [
            'sector' => 'namangan', 'position' => 'chief', 'email' => 'b.nabiev@cerr.uz',
            'name' => ['kr' => 'Набиев Бобур Мухаммадкосим угли', 'ru' => 'Набиев Бобур Мухаммадкосим угли', 'uz' => 'Nabiyev Bobur Muxammadkosim o‘g‘li', 'en' => 'Bobur Nabiev'],
        ],
        [
            'sector' => 'namangan', 'position' => 'leading', 'email' => 's.ergashev@cerr.uz',
            'name' => ['kr' => 'Эргашев Санжарбек Салимович', 'ru' => 'Эргашев Санжарбек Салимович', 'uz' => 'Ergashev Sanjarbek Salimovich', 'en' => 'Sanjarbek Ergashev'],
        ],
        // ---- Samarkand branch ------------------------------------------------------
        [
            'sector' => 'samarkand', 'is_head' => true, 'position' => 'head', 'email' => 'k.nurmurodov@cerr.uz',
            'name' => ['kr' => 'Нурмуродов Камолиддин Пирмаматович', 'ru' => 'Нурмуродов Камолиддин Пирмаматович', 'uz' => 'Nurmurodov Kamoliddin Pirmamatovich', 'en' => 'Kamoliddin Nurmurodov'],
        ],
        [
            'sector' => 'samarkand', 'position' => 'chief', 'email' => 'a.murtazaev@cerr.uz',
            'name' => ['kr' => 'Муртазаев Ардашер Зафар ўғли', 'ru' => 'Муртазаев Ардашер Зафар ўғли', 'uz' => 'Murtazayev Ardasher Zafar o‘g‘li', 'en' => 'Ardasher Murtazaev'],
        ],
        [
            'sector' => 'samarkand', 'position' => 'leading', 'email' => 'g.tursunqulov@cerr.uz',
            'name' => ['kr' => 'Турсункулов Голибжон Шавкат ўғли', 'ru' => 'Турсункулов Голибжон Шавкат ўғли', 'uz' => 'Tursunkulov Golibjon Shavkat o‘g‘li', 'en' => 'Golibjon Tursunkulov'],
        ],
        // ---- Kashkadarya branch ----------------------------------------------------
        [
            'sector' => 'kashkadarya', 'is_head' => true, 'position' => 'head', 'email' => 'e.zayniddinov@cerr.uz',
            'name' => ['kr' => 'Зайниддинов Элдор Эркинович', 'ru' => 'Зайниддинов Элдор Эркинович', 'uz' => 'Zayniddinov Eldor Erkinovich', 'en' => 'Eldor Zayniddinov'],
        ],
        [
            'sector' => 'kashkadarya', 'position' => 'chief', 'email' => 'f.islomov@cerr.uz',
            'name' => ['kr' => 'Исломов Фурқат Усмон ўғли', 'ru' => 'Исломов Фурқат Усмон ўғли', 'uz' => 'Islomov Furqat Usmon o‘g‘li', 'en' => 'Furkat Islomov'],
        ],
        [
            'sector' => 'kashkadarya', 'position' => 'leading', 'email' => 's.saxipov@cerr.uz',
            'name' => ['kr' => 'Сахипов Саидолим Саидкосимович', 'ru' => 'Сахипов Саидолим Саидкосимович', 'uz' => 'Saxipov Saidolim Saidkosimovich', 'en' => 'Saidolim Sakhipov'],
        ],
    ];
}
