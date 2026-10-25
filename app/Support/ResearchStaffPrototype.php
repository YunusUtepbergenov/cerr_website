<?php

namespace App\Support;

use Illuminate\Support\Collection;
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
 * localised values carry `kr`, `ru` and `en` keys; Latin Uzbek falls back
 * through {@see self::text()}. Everything here is placeholder content and
 * will be replaced by real models and an admin form.
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
    /** @var list<array{key: string, slug: string, accent: string}> */
    public const SECTORS = [
        ['key' => 'macro', 'slug' => 'macroeconomic-policy', 'accent' => '#1b69c4'],
        ['key' => 'bank', 'slug' => 'banking-and-capital-markets', 'accent' => '#0f766e'],
        ['key' => 'competitiveness', 'slug' => 'competitiveness-and-investment', 'accent' => '#b45309'],
        ['key' => 'foreign_economic', 'slug' => 'foreign-economic-activity', 'accent' => '#6d28d9'],
        ['key' => 'cluster', 'slug' => 'industrial-clusters', 'accent' => '#be123c'],
        ['key' => 'regions', 'slug' => 'regional-development', 'accent' => '#15803d'],
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
     * @return Collection<int, array{key: string, slug: string, accent: string, head: ?Person, staff: Collection<int, Person>}>
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

    /** @return ?array{key: string, slug: string, accent: string} */
    public static function sector(string $key): ?array
    {
        return collect(self::SECTORS)->firstWhere('key', $key);
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
        // ---- Macroeconomic policy ------------------------------------------------
        [
            'sector' => 'macro', 'is_head' => true, 'position' => 'head', 'degree' => 'doc',
            'name' => ['kr' => 'Абдуллаев Бахтиёр Рустамович', 'ru' => 'Абдуллаев Бахтиёр Рустамович', 'en' => 'Bakhtiyor Abdullaev'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Университет мировой экономики и дипломатии, «Мировая экономика», бакалавр экономики, 2003', 'en' => 'University of World Economy and Diplomacy, World Economy, BA in Economics, 2003'],
            'master' => ['ru' => 'Университет мировой экономики и дипломатии, «Макроэкономика», магистр экономики, 2005', 'en' => 'University of World Economy and Diplomacy, Macroeconomics, MA in Economics, 2005'],
            'doctorate' => ['ru' => 'Ташкентский государственный экономический университет, 08.00.01 — Экономическая теория, 2009–2012', 'en' => 'Tashkent State University of Economics, 08.00.01 — Economic Theory, 2009–2012'],
            'degree_title' => ['ru' => 'Доктор экономических наук (DSc), 08.00.01 — Экономическая теория, 2013; старший научный сотрудник', 'en' => 'Doctor of Economics (DSc), 08.00.01 — Economic Theory, 2013; Senior Research Fellow'],
            'research_areas' => [
                'ru' => ['Макроэкономическое прогнозирование', 'Бюджетная политика', 'Инфляционное таргетирование', 'DSGE-модели', 'Фискально-монетарная координация'],
                'en' => ['Macroeconomic forecasting', 'Fiscal policy', 'Inflation targeting', 'DSGE models', 'Fiscal–monetary coordination'],
            ],
            'international' => [
                ['years' => '2022', 'text' => ['ru' => 'Программа технической помощи МВФ по среднесрочному бюджетному планированию, Ташкент — Вена', 'en' => 'IMF technical-assistance programme on medium-term fiscal frameworks, Tashkent — Vienna']],
                ['years' => '2018–2019', 'text' => ['ru' => 'Совместный проект с Азиатским банком развития по моделированию налоговой реформы', 'en' => 'Joint project with the Asian Development Bank on tax-reform modelling']],
                ['years' => '2016', 'text' => ['ru' => 'Стажировка в Национальном банке Польши (прогнозирование и моделирование)', 'en' => 'Research visit to the National Bank of Poland (forecasting and modelling)']],
            ],
            'achievements' => [
                ['years' => '2023', 'text' => ['ru' => 'Нагрудный знак «Ўзбекистон Республикаси фан арбоби»', 'en' => '“Distinguished Scientist of the Republic of Uzbekistan” badge']],
                ['years' => '2020', 'text' => ['ru' => 'Грант Фонда поддержки инноваций на разработку квартальной прогнозной модели', 'en' => 'Innovation Fund grant for the quarterly forecasting model']],
            ],
            'previous_experience' => [
                ['years' => '2008–2014', 'text' => ['ru' => 'Главный специалист, Министерство экономики Республики Узбекистан', 'en' => 'Chief Specialist, Ministry of Economy of the Republic of Uzbekistan']],
                ['years' => '2005–2008', 'text' => ['ru' => 'Экономист, Департамент макроэкономического анализа, Центральный банк', 'en' => 'Economist, Macroeconomic Analysis Department, Central Bank']],
            ],
            'articles' => [
                ['text' => 'Абдуллаев Б. Р. Согласованность бюджетной и денежно-кредитной политики в условиях перехода к инфляционному таргетированию // Экономическое обозрение. — 2024. — № 3. — С. 12–27.', 'url' => 'https://review.uz'],
                ['text' => 'Abdullaev B., Karimova N. Fiscal multipliers in a small open economy: evidence from Uzbekistan // Central Asian Economic Review. — 2023. — Vol. 8 (2). — P. 45–68.', 'url' => 'https://doi.org/10.0000/example.2023.0045'],
                ['text' => 'Abdullaev B. Potential output and the output gap in Uzbekistan: a multivariate filter approach // Journal of Asian Economics. — 2021. — Vol. 76.'],
            ],
            'books' => [
                ['text' => ['ru' => 'Абдуллаев Б. Р. Среднесрочный прогноз макроэкономических параметров: методология ЦЭИР. — Ташкент: ЦЭИР, 2022. — 96 с.', 'en' => 'Abdullaev B. Medium-term macroeconomic forecasting: the CERR methodology. — Tashkent: CERR, 2022. — 96 p.']],
            ],
            'reports' => [
                ['text' => ['ru' => 'Макроэкономический обзор Узбекистана за 2024 год. — ЦЭИР, 2025.', 'en' => 'Uzbekistan Macroeconomic Review 2024. — CERR, 2025.'], 'url' => 'https://cerr.uz'],
                ['text' => ['ru' => 'Сценарии бюджетной консолидации на 2025–2027 годы. — ЦЭИР, 2024.', 'en' => 'Fiscal consolidation scenarios for 2025–2027. — CERR, 2024.']],
            ],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0000', 'scholar' => 'https://scholar.google.com/', 'scopus' => 'https://www.scopus.com/'],
        ],
        [
            'sector' => 'macro', 'position' => 'leading', 'degree' => 'phd',
            'name' => ['ru' => 'Каримова Нилуфар Шавкатовна', 'en' => 'Nilufar Karimova'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2011', 'en' => 'Tashkent State University of Economics, Economics, 2011'],
            'master' => ['ru' => 'Банковско-финансовая академия, «Денежно-кредитная политика», 2013', 'en' => 'Banking and Finance Academy, Monetary Policy, 2013'],
            'doctorate' => ['ru' => 'Банковско-финансовая академия, 08.00.07 — Финансы, денежное обращение и кредит, 2013–2017', 'en' => 'Banking and Finance Academy, 08.00.07 — Finance, Money and Credit, 2013–2017'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2017', 'en' => 'PhD in Economics, 2017'],
            'research_areas' => ['ru' => ['Инфляция', 'Трансмиссионный механизм', 'Эконометрика временных рядов'], 'en' => ['Inflation', 'Transmission mechanism', 'Time-series econometrics']],
            'international' => [['years' => '2021', 'text' => ['ru' => 'Курс Института МВФ «Прогнозирование и анализ денежно-кредитной политики», Вена', 'en' => 'IMF Institute course “Monetary Policy Analysis and Forecasting”, Vienna']]],
            'articles' => [
                ['text' => 'Karimova N. Exchange-rate pass-through to consumer prices in Uzbekistan // Journal of Asian Economics. — 2024. — Vol. 91.', 'url' => 'https://doi.org/10.0000/example.2024.0091'],
                ['text' => 'Каримова Н. Ш. Инфляционные ожидания домохозяйств: результаты опроса ЦЭИР // Экономическое обозрение. — 2023. — № 6.'],
            ],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0001', 'scholar' => 'https://scholar.google.com/'],
        ],
        [
            'sector' => 'macro', 'position' => 'senior',
            'name' => ['ru' => 'Юсупов Жасур Акмалович', 'en' => 'Jasur Yusupov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2014', 'en' => 'Tashkent State University of Economics, Economics, 2014'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Макроэкономика», 2016', 'en' => 'Tashkent State University of Economics, Macroeconomics, 2016'],
            'research_areas' => ['ru' => ['Платёжный баланс', 'Внешний долг', 'Сценарное моделирование'], 'en' => ['Balance of payments', 'External debt', 'Scenario modelling']],
            'reports' => [['text' => ['ru' => 'Устойчивость внешнего долга Узбекистана: стресс-тесты. — ЦЭИР, 2024.', 'en' => 'External-debt sustainability of Uzbekistan: stress tests. — CERR, 2024.']]],
        ],
        [
            'sector' => 'macro', 'position' => 'researcher',
            'name' => ['ru' => 'Рахимова Дилноза Бахтиёровна', 'en' => 'Dilnoza Rakhimova'],
            'bachelor' => ['ru' => 'Вестминстерский международный университет в Ташкенте, «Экономика», 2019', 'en' => 'Westminster International University in Tashkent, Economics, 2019'],
            'master' => ['ru' => 'Вестминстерский международный университет в Ташкенте, «Прикладная экономика», 2021', 'en' => 'Westminster International University in Tashkent, Applied Economics, 2021'],
            'research_areas' => ['ru' => ['Потенциальный ВВП', 'Фильтрация временных рядов'], 'en' => ['Potential GDP', 'Time-series filtering']],
        ],
        [
            'sector' => 'macro', 'position' => 'junior',
            'name' => ['ru' => 'Тошматов Отабек Улугбекович', 'en' => 'Otabek Toshmatov'],
            'bachelor' => ['ru' => 'Университет мировой экономики и дипломатии, «Международная экономика», 2024', 'en' => 'University of World Economy and Diplomacy, International Economics, 2024'],
            'research_areas' => ['ru' => ['Макроэкономическая статистика', 'Визуализация данных'], 'en' => ['Macroeconomic statistics', 'Data visualisation']],
        ],

        // ---- Banking, finance and capital markets ---------------------------------
        [
            'sector' => 'bank', 'is_head' => true, 'position' => 'head', 'degree' => 'cand',
            'name' => ['ru' => 'Мирзаев Шерзод Анварович', 'en' => 'Sherzod Mirzaev'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Ташкентский финансовый институт, «Банковское дело», 2003', 'en' => 'Tashkent Institute of Finance, Banking, 2003'],
            'master' => ['ru' => 'Банковско-финансовая академия, «Банковское дело», 2005', 'en' => 'Banking and Finance Academy, Banking, 2005'],
            'doctorate' => ['ru' => 'Банковско-финансовая академия, 08.00.07, 2006–2009', 'en' => 'Banking and Finance Academy, 08.00.07, 2006–2009'],
            'degree_title' => ['ru' => 'Кандидат экономических наук, 08.00.07 — Финансы, денежное обращение и кредит, 2009; доцент', 'en' => 'Candidate of Economic Sciences, 08.00.07 — Finance, Money and Credit, 2009; Associate Professor'],
            'research_areas' => ['ru' => ['Банковское регулирование', 'Рынок капитала', 'Финансовая доступность', 'Финтех'], 'en' => ['Banking regulation', 'Capital markets', 'Financial inclusion', 'Fintech']],
            'international' => [
                ['years' => '2023', 'text' => ['ru' => 'Проект Всемирного банка по оценке доступа МСБ к финансированию', 'en' => 'World Bank project on SME access to finance']],
                ['years' => '2019', 'text' => ['ru' => 'Стажировка в Бундесбанке (надзор и финансовая стабильность), Франкфурт', 'en' => 'Secondment at the Deutsche Bundesbank (supervision and financial stability), Frankfurt']],
            ],
            'achievements' => [['years' => '2021', 'text' => ['ru' => 'Почётная грамота Центрального банка за вклад в реформу банковского сектора', 'en' => 'Central Bank certificate of honour for contribution to banking reform']]],
            'previous_experience' => [
                ['years' => '2005–2012', 'text' => ['ru' => 'Начальник отдела, Центральный банк Республики Узбекистан', 'en' => 'Head of Division, Central Bank of the Republic of Uzbekistan']],
            ],
            'articles' => [
                ['text' => 'Мирзаев Ш. А. Трансформация банковского сектора Узбекистана: итоги приватизации // Экономическое обозрение. — 2025. — № 1.'],
                ['text' => 'Mirzaev S., Nazarova G. Credit to SMEs and regional growth in Uzbekistan // Emerging Markets Finance and Trade. — 2023.', 'url' => 'https://doi.org/10.0000/example.2023.emft'],
            ],
            'books' => [['text' => ['ru' => 'Мирзаев Ш. А. Банковская система Узбекистана: от реформы к росту. — Ташкент: Iqtisodiyot, 2021. — 212 с.', 'en' => 'Mirzaev S. The banking system of Uzbekistan: from reform to growth. — Tashkent: Iqtisodiyot, 2021. — 212 p.']]],
            'reports' => [['text' => ['ru' => 'Индекс доступности финансовых услуг в регионах. — ЦЭИР, 2024.', 'en' => 'Financial-services accessibility index by region. — CERR, 2024.'], 'url' => 'https://cerr.uz']],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0002', 'scopus' => 'https://www.scopus.com/'],
        ],
        [
            'sector' => 'bank', 'position' => 'leading', 'degree' => 'phd',
            'name' => ['ru' => 'Назарова Гульнора Тохировна', 'en' => 'Gulnora Nazarova'],
            'bachelor' => ['ru' => 'Ташкентский финансовый институт, «Финансы», 2010', 'en' => 'Tashkent Institute of Finance, Finance, 2010'],
            'master' => ['ru' => 'Ташкентский финансовый институт, «Рынок ценных бумаг», 2012', 'en' => 'Tashkent Institute of Finance, Securities Markets, 2012'],
            'doctorate' => ['ru' => 'Ташкентский финансовый институт, 08.00.07, 2012–2016', 'en' => 'Tashkent Institute of Finance, 08.00.07, 2012–2016'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2016', 'en' => 'PhD in Economics, 2016'],
            'research_areas' => ['ru' => ['Фондовый рынок', 'Корпоративные облигации', 'IPO'], 'en' => ['Stock market', 'Corporate bonds', 'IPOs']],
            'articles' => [['text' => 'Nazarova G. Determinants of IPO activity in frontier markets // Review of Development Finance. — 2024.', 'url' => 'https://doi.org/10.0000/example.2024.rdf']],
            'profiles' => ['scholar' => 'https://scholar.google.com/'],
        ],
        [
            'sector' => 'bank', 'position' => 'senior',
            'name' => ['ru' => 'Хакимов Фаррух Баходирович', 'en' => 'Farrukh Khakimov'],
            'bachelor' => ['ru' => 'Банковско-финансовая академия, «Банковское дело», 2015', 'en' => 'Banking and Finance Academy, Banking, 2015'],
            'master' => ['ru' => 'Банковско-финансовая академия, «Банковское дело», 2017', 'en' => 'Banking and Finance Academy, Banking, 2017'],
            'research_areas' => ['ru' => ['Кредитование МСБ', 'Процентная политика'], 'en' => ['SME lending', 'Interest-rate policy']],
        ],
        [
            'sector' => 'bank', 'position' => 'researcher',
            'name' => ['ru' => 'Исмоилова Мадина Рустамовна', 'en' => 'Madina Ismoilova'],
            'bachelor' => ['ru' => 'Ташкентский финансовый институт, «Финансы», 2020', 'en' => 'Tashkent Institute of Finance, Finance, 2020'],
            'master' => ['ru' => 'Ташкентский финансовый институт, «Цифровые финансы», 2022', 'en' => 'Tashkent Institute of Finance, Digital Finance, 2022'],
            'research_areas' => ['ru' => ['Цифровые платежи', 'Финтех'], 'en' => ['Digital payments', 'Fintech']],
        ],

        // ---- Competitiveness and investment ----------------------------------------
        [
            'sector' => 'competitiveness', 'is_head' => true, 'position' => 'head', 'degree' => 'phd',
            'name' => ['ru' => 'Салимов Жамшид Каримович', 'en' => 'Jamshid Salimov'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2008', 'en' => 'Tashkent State University of Economics, Economics, 2008'],
            'master' => ['ru' => 'Университет Цукубы (Япония), «Экономическая и государственная политика», 2010', 'en' => 'University of Tsukuba (Japan), Economic and Public Policy, 2010'],
            'doctorate' => ['ru' => 'Университет Цукубы (Япония), экономика, 2010–2014', 'en' => 'University of Tsukuba (Japan), Economics, 2010–2014'],
            'degree_title' => ['ru' => 'PhD по экономике, Университет Цукубы, 2014', 'en' => 'PhD in Economics, University of Tsukuba, 2014'],
            'research_areas' => ['ru' => ['Промышленная политика', 'Производительность', 'Инвестиционный климат', 'Индексы деловой активности'], 'en' => ['Industrial policy', 'Productivity', 'Investment climate', 'Business activity indices']],
            'international' => [
                ['years' => '2024', 'text' => ['ru' => 'Совместное исследование с Berlin Economics по конкурентоспособности промышленности', 'en' => 'Joint study with Berlin Economics on industrial competitiveness']],
                ['years' => '2020–2022', 'text' => ['ru' => 'Проект ЮНИДО по промышленной статистике и диагностике', 'en' => 'UNIDO project on industrial statistics and diagnostics']],
            ],
            'achievements' => [['years' => '2022', 'text' => ['ru' => 'Премия JICA за лучшее прикладное исследование выпускника', 'en' => 'JICA alumni award for best applied research']]],
            'previous_experience' => [['years' => '2014–2015', 'text' => ['ru' => 'Научный сотрудник, Институт прогнозирования и макроэкономических исследований', 'en' => 'Researcher, Institute of Forecasting and Macroeconomic Research']]],
            'articles' => [
                ['text' => 'Salimov J. Firm-level productivity and trade liberalisation in Uzbekistan // World Development. — 2024.', 'url' => 'https://doi.org/10.0000/example.2024.wd'],
                ['text' => 'Салимов Ж. К. Индекс деловой активности: методология и первые результаты // Экономическое обозрение. — 2022. — № 9.'],
            ],
            'reports' => [['text' => ['ru' => 'Индекс деловой активности: ежемесячный выпуск. — ЦЭИР, 2022–2025.', 'en' => 'Business Activity Index: monthly release. — CERR, 2022–2025.'], 'url' => 'https://cerr.uz']],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0003', 'scholar' => 'https://scholar.google.com/', 'scopus' => 'https://www.scopus.com/'],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'senior', 'degree' => 'phd',
            'name' => ['ru' => 'Эргашева Малика Алишеровна', 'en' => 'Malika Ergasheva'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2012', 'en' => 'Tashkent State University of Economics, Economics, 2012'],
            'master' => ['ru' => 'Университет Варвика (Великобритания), «Экономика», 2014', 'en' => 'University of Warwick (UK), Economics, 2014'],
            'doctorate' => ['ru' => 'Ташкентский государственный экономический университет, 08.00.03, 2014–2018', 'en' => 'Tashkent State University of Economics, 08.00.03, 2014–2018'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2018', 'en' => 'PhD in Economics, 2018'],
            'research_areas' => ['ru' => ['Прямые иностранные инвестиции', 'Специальные экономические зоны'], 'en' => ['Foreign direct investment', 'Special economic zones']],
            'articles' => [['text' => 'Ergasheva M. Do special economic zones attract FDI? Evidence from Uzbekistan // Post-Communist Economies. — 2023.']],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'researcher',
            'name' => ['ru' => 'Умаров Давлат Шухратович', 'en' => 'Davlat Umarov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2018', 'en' => 'Tashkent State University of Economics, Economics, 2018'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика промышленности», 2020', 'en' => 'Tashkent State University of Economics, Industrial Economics, 2020'],
            'research_areas' => ['ru' => ['Отраслевая конкурентоспособность', 'Экспортная диверсификация'], 'en' => ['Sectoral competitiveness', 'Export diversification']],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'researcher',
            'name' => ['ru' => 'Азизова Севара Фарходовна', 'en' => 'Sevara Azizova'],
            'bachelor' => ['ru' => 'Вестминстерский международный университет в Ташкенте, «Экономика», 2019', 'en' => 'Westminster International University in Tashkent, Economics, 2019'],
            'master' => ['ru' => 'Университет Инха в Ташкенте, «Бизнес-аналитика», 2021', 'en' => 'Inha University in Tashkent, Business Analytics, 2021'],
            'research_areas' => ['ru' => ['Опросы предприятий', 'Деловой климат'], 'en' => ['Enterprise surveys', 'Business climate']],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'junior',
            'name' => ['ru' => 'Нурматов Бекзод Икромович', 'en' => 'Bekzod Nurmatov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2023', 'en' => 'Tashkent State University of Economics, Economics, 2023'],
            'research_areas' => ['ru' => ['Производительность труда', 'Отраслевая статистика'], 'en' => ['Labour productivity', 'Industry statistics']],
        ],

        // ---- Foreign economic activity and integration ------------------------------
        [
            'sector' => 'foreign_economic', 'is_head' => true, 'position' => 'head', 'degree' => 'doc',
            'name' => ['ru' => 'Ахмедова Наргиза Абдуллаевна', 'en' => 'Nargiza Akhmedova'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Университет мировой экономики и дипломатии, «Международные экономические отношения», 2001', 'en' => 'University of World Economy and Diplomacy, International Economic Relations, 2001'],
            'master' => ['ru' => 'Университет мировой экономики и дипломатии, «Международная торговля», 2003', 'en' => 'University of World Economy and Diplomacy, International Trade, 2003'],
            'doctorate' => ['ru' => 'Университет мировой экономики и дипломатии, 08.00.09 — Мировая экономика, 2008–2011', 'en' => 'University of World Economy and Diplomacy, 08.00.09 — World Economy, 2008–2011'],
            'degree_title' => ['ru' => 'Доктор экономических наук (DSc), 08.00.09 — Мировая экономика, 2012; профессор', 'en' => 'Doctor of Economics (DSc), 08.00.09 — World Economy, 2012; Professor'],
            'research_areas' => ['ru' => ['Внешняя торговля', 'Присоединение к ВТО', 'Торговые соглашения', 'Региональная интеграция'], 'en' => ['Foreign trade', 'WTO accession', 'Trade agreements', 'Regional integration']],
            'international' => [
                ['years' => '2023–2025', 'text' => ['ru' => 'Экспертная поддержка переговоров о присоединении к ВТО (программа ЮСАИД)', 'en' => 'Expert support to WTO accession negotiations (USAID programme)']],
                ['years' => '2017', 'text' => ['ru' => 'Стажировка в Секретариате ВТО, Женева', 'en' => 'Fellowship at the WTO Secretariat, Geneva']],
            ],
            'achievements' => [['years' => '2024', 'text' => ['ru' => 'Орден «Дўстлик»', 'en' => '“Dustlik” Order']]],
            'previous_experience' => [
                ['years' => '2011–2013', 'text' => ['ru' => 'Заведующая кафедрой, Университет мировой экономики и дипломатии', 'en' => 'Head of Department, University of World Economy and Diplomacy']],
                ['years' => '2003–2008', 'text' => ['ru' => 'Главный специалист, Министерство внешних экономических связей', 'en' => 'Chief Specialist, Ministry of Foreign Economic Relations']],
            ],
            'articles' => [
                ['text' => 'Akhmedova N. Trade-cost effects of WTO accession for landlocked economies // The World Economy. — 2024.', 'url' => 'https://doi.org/10.0000/example.2024.twe'],
                ['text' => 'Ахмедова Н. А. Транспортные коридоры Центральной Азии: оценка выигрыша от снижения торговых издержек // Экономическое обозрение. — 2023. — № 2.'],
            ],
            'books' => [['text' => ['ru' => 'Ахмедова Н. А. Узбекистан и ВТО: экономика присоединения. — Ташкент: Fan, 2023. — 180 с.', 'en' => 'Akhmedova N. Uzbekistan and the WTO: the economics of accession. — Tashkent: Fan, 2023. — 180 p.']]],
            'reports' => [['text' => ['ru' => 'Оценка эффектов соглашения о свободной торговле с ЕАЭС. — ЦЭИР, 2024.', 'en' => 'Impact assessment of a free-trade agreement with the EAEU. — CERR, 2024.']]],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0004', 'scopus' => 'https://www.scopus.com/'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'leading', 'degree' => 'phd',
            'name' => ['ru' => 'Собиров Улугбек Шерзодович', 'en' => 'Ulugbek Sobirov'],
            'bachelor' => ['ru' => 'Университет мировой экономики и дипломатии, «Мировая экономика», 2010', 'en' => 'University of World Economy and Diplomacy, World Economy, 2010'],
            'master' => ['ru' => 'Университет мировой экономики и дипломатии, «Международная торговля», 2012', 'en' => 'University of World Economy and Diplomacy, International Trade, 2012'],
            'doctorate' => ['ru' => 'Университет мировой экономики и дипломатии, 08.00.09, 2012–2016', 'en' => 'University of World Economy and Diplomacy, 08.00.09, 2012–2016'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2016', 'en' => 'PhD in Economics, 2016'],
            'research_areas' => ['ru' => ['Гравитационные модели торговли', 'Нетарифные барьеры'], 'en' => ['Gravity models of trade', 'Non-tariff barriers']],
            'articles' => [['text' => 'Sobirov U. Non-tariff measures and export performance in Central Asia // Journal of Asian Economics. — 2022.']],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'senior',
            'name' => ['ru' => 'Жураева Зилола Нодировна', 'en' => 'Zilola Juraeva'],
            'bachelor' => ['ru' => 'Университет мировой экономики и дипломатии, «Мировая экономика», 2015', 'en' => 'University of World Economy and Diplomacy, World Economy, 2015'],
            'master' => ['ru' => 'Университет мировой экономики и дипломатии, «Международная экономика», 2017', 'en' => 'University of World Economy and Diplomacy, International Economics, 2017'],
            'research_areas' => ['ru' => ['Транспортные коридоры', 'Логистика'], 'en' => ['Transport corridors', 'Logistics']],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'researcher',
            'name' => ['ru' => 'Кодиров Санжар Муродович', 'en' => 'Sanjar Kodirov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2020', 'en' => 'Tashkent State University of Economics, Economics, 2020'],
            'master' => ['ru' => 'Университет мировой экономики и дипломатии, «Международная торговля», 2022', 'en' => 'University of World Economy and Diplomacy, International Trade, 2022'],
            'research_areas' => ['ru' => ['Торговая статистика', 'Экспорт услуг'], 'en' => ['Trade statistics', 'Services exports']],
        ],

        // ---- Industrial and agricultural clusters -----------------------------------
        [
            'sector' => 'cluster', 'is_head' => true, 'position' => 'head', 'degree' => 'cand',
            'name' => ['ru' => 'Ташкентов Аброр Хамидович', 'en' => 'Abror Tashkentov'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Ташкентский государственный аграрный университет, «Экономика АПК», 2002', 'en' => 'Tashkent State Agrarian University, Agricultural Economics, 2002'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2004', 'en' => 'Tashkent State University of Economics, Economics, 2004'],
            'doctorate' => ['ru' => 'Ташкентский государственный экономический университет, 08.00.03, 2005–2008', 'en' => 'Tashkent State University of Economics, 08.00.03, 2005–2008'],
            'degree_title' => ['ru' => 'Кандидат экономических наук, 08.00.03 — Отраслевая экономика, 2008', 'en' => 'Candidate of Economic Sciences, 08.00.03 — Sectoral Economics, 2008'],
            'research_areas' => ['ru' => ['Агрокластеры', 'Цепочки добавленной стоимости', 'Экспортный потенциал отраслей'], 'en' => ['Agro-clusters', 'Value chains', 'Sectoral export potential']],
            'international' => [['years' => '2021–2023', 'text' => ['ru' => 'Проект ФАО по развитию цепочек добавленной стоимости в плодоовощной отрасли', 'en' => 'FAO project on horticultural value-chain development']]],
            'achievements' => [['years' => '2019', 'text' => ['ru' => 'Благодарность Министерства сельского хозяйства', 'en' => 'Letter of appreciation, Ministry of Agriculture']]],
            'previous_experience' => [['years' => '2008–2016', 'text' => ['ru' => 'Начальник управления, Министерство сельского и водного хозяйства', 'en' => 'Head of Department, Ministry of Agriculture and Water Resources']]],
            'articles' => [['text' => 'Ташкентов А. Х. Экономическая эффективность хлопково-текстильных кластеров // Экономическое обозрение. — 2024. — № 5.']],
            'reports' => [['text' => ['ru' => 'Кластеры Узбекистана: карта и показатели. — ЦЭИР, 2023.', 'en' => 'Clusters of Uzbekistan: map and indicators. — CERR, 2023.'], 'url' => 'https://cerr.uz']],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0005'],
        ],
        [
            'sector' => 'cluster', 'position' => 'senior',
            'name' => ['ru' => 'Холматова Шахноза Равшановна', 'en' => 'Shakhnoza Kholmatova'],
            'bachelor' => ['ru' => 'Ташкентский государственный аграрный университет, «Экономика АПК», 2014', 'en' => 'Tashkent State Agrarian University, Agricultural Economics, 2014'],
            'master' => ['ru' => 'Ташкентский государственный аграрный университет, «Агробизнес», 2016', 'en' => 'Tashkent State Agrarian University, Agribusiness, 2016'],
            'research_areas' => ['ru' => ['Плодоовощной экспорт', 'Кооперация фермеров'], 'en' => ['Horticultural exports', 'Farmer cooperation']],
        ],
        [
            'sector' => 'cluster', 'position' => 'researcher',
            'name' => ['ru' => 'Бобоев Комил Турсунович', 'en' => 'Komil Boboev'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2019', 'en' => 'Tashkent State University of Economics, Economics, 2019'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика промышленности», 2021', 'en' => 'Tashkent State University of Economics, Industrial Economics, 2021'],
            'research_areas' => ['ru' => ['Текстильные кластеры', 'Локализация производства'], 'en' => ['Textile clusters', 'Production localisation']],
        ],

        // ---- Regional development --------------------------------------------------
        [
            'sector' => 'regions', 'is_head' => true, 'position' => 'head', 'degree' => 'phd',
            'name' => ['ru' => 'Мамадалиев Санжарбек Мухтарович', 'en' => 'Sanjarbek Mamadaliev'],
            'phone' => '+998 78 150-02-02',
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2007', 'en' => 'Tashkent State University of Economics, Economics, 2007'],
            'master' => ['ru' => 'Университет Хитоцубаси (Япония), «Государственная политика», 2010', 'en' => 'Hitotsubashi University (Japan), Public Policy, 2010'],
            'doctorate' => ['ru' => 'Ташкентский государственный экономический университет, 08.00.03, 2011–2014', 'en' => 'Tashkent State University of Economics, 08.00.03, 2011–2014'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2014', 'en' => 'PhD in Economics, 2014'],
            'research_areas' => ['ru' => ['Региональная экономика', 'Рынок труда', 'Бедность и неравенство', 'Рейтинги регионов'], 'en' => ['Regional economics', 'Labour market', 'Poverty and inequality', 'Regional rankings']],
            'international' => [['years' => '2022–2024', 'text' => ['ru' => 'Проект ПРООН по многомерному измерению бедности', 'en' => 'UNDP project on multidimensional poverty measurement']]],
            'achievements' => [['years' => '2023', 'text' => ['ru' => 'Грант Всемирного банка на разработку индекса развития районов', 'en' => 'World Bank grant for the district development index']]],
            'previous_experience' => [['years' => '2010–2015', 'text' => ['ru' => 'Ведущий специалист, Министерство экономики', 'en' => 'Leading Specialist, Ministry of Economy']]],
            'articles' => [
                ['text' => 'Mamadaliev S. Spatial inequality and internal migration in Uzbekistan // Regional Studies. — 2024.', 'url' => 'https://doi.org/10.0000/example.2024.rs'],
                ['text' => 'Мамадалиев С. М. Рейтинг социально-экономического развития районов: методология // Экономическое обозрение. — 2023. — № 8.'],
            ],
            'reports' => [['text' => ['ru' => 'Рейтинг районов и городов Узбекистана за 2024 год. — ЦЭИР, 2025.', 'en' => 'Ranking of districts and cities of Uzbekistan, 2024. — CERR, 2025.'], 'url' => 'https://cerr.uz']],
            'profiles' => ['orcid' => 'https://orcid.org/0000-0000-0000-0006', 'scholar' => 'https://scholar.google.com/'],
        ],
        [
            'sector' => 'regions', 'position' => 'leading', 'degree' => 'phd',
            'name' => ['ru' => 'Расулова Феруза Хасановна', 'en' => 'Feruza Rasulova'],
            'bachelor' => ['ru' => 'Самаркандский институт экономики и сервиса, «Экономика», 2011', 'en' => 'Samarkand Institute of Economics and Service, Economics, 2011'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Региональная экономика», 2013', 'en' => 'Tashkent State University of Economics, Regional Economics, 2013'],
            'doctorate' => ['ru' => 'Ташкентский государственный экономический университет, 08.00.03, 2013–2017', 'en' => 'Tashkent State University of Economics, 08.00.03, 2013–2017'],
            'degree_title' => ['ru' => 'Доктор философии (PhD) по экономическим наукам, 2017', 'en' => 'PhD in Economics, 2017'],
            'research_areas' => ['ru' => ['Занятость', 'Трудовая миграция'], 'en' => ['Employment', 'Labour migration']],
            'articles' => [['text' => 'Rasulova F. Remittances and household welfare in rural Uzbekistan // Journal of Development Studies. — 2023.']],
        ],
        [
            'sector' => 'regions', 'position' => 'senior',
            'name' => ['ru' => 'Турсунов Шохрух Бахромович', 'en' => 'Shokhrukh Tursunov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2015', 'en' => 'Tashkent State University of Economics, Economics, 2015'],
            'master' => ['ru' => 'Ташкентский государственный экономический университет, «Экономическая статистика», 2017', 'en' => 'Tashkent State University of Economics, Economic Statistics, 2017'],
            'research_areas' => ['ru' => ['Муниципальная статистика', 'Индексы развития'], 'en' => ['Municipal statistics', 'Development indices']],
        ],
        [
            'sector' => 'regions', 'position' => 'researcher',
            'name' => ['ru' => 'Хасанова Муниса Азаматовна', 'en' => 'Munisa Khasanova'],
            'bachelor' => ['ru' => 'Вестминстерский международный университет в Ташкенте, «Экономика», 2020', 'en' => 'Westminster International University in Tashkent, Economics, 2020'],
            'master' => ['ru' => 'Университет Инха в Ташкенте, «Аналитика данных», 2022', 'en' => 'Inha University in Tashkent, Data Analytics, 2022'],
            'research_areas' => ['ru' => ['Бедность', 'Социальная защита'], 'en' => ['Poverty', 'Social protection']],
        ],
        [
            'sector' => 'regions', 'position' => 'junior',
            'name' => ['ru' => 'Эшонкулов Жавохир Собирович', 'en' => 'Javokhir Eshonkulov'],
            'bachelor' => ['ru' => 'Ташкентский государственный экономический университет, «Экономика», 2024', 'en' => 'Tashkent State University of Economics, Economics, 2024'],
            'research_areas' => ['ru' => ['Региональная статистика', 'ГИС-анализ'], 'en' => ['Regional statistics', 'GIS analysis']],
        ],
    ];
}
