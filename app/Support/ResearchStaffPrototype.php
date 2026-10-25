<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Sample data behind the research-staff prototype pages.
 *
 * Everything here is placeholder content so the listing and profile layouts
 * can be designed and reviewed. It will be replaced by real models and an
 * admin form once the employee questionnaire (анкета) fields are agreed.
 *
 * Localised values are arrays keyed by locale; see {@see self::text()} for
 * the fallback order (kr falls back to ru, uz to en).
 *
 * @phpstan-type Localised array<string, string>
 * @phpstan-type Entry array{years: string, text: Localised}
 * @phpstan-type Person array{
 *     slug: string, sector: string, is_head: bool, position: string, degree: ?string,
 *     email: string, phone: ?string, photo: ?string, since: ?int,
 *     name: Localised, about: Localised, languages: Localised,
 *     interests: array<string, list<string>>, education: list<Entry>, experience: list<Entry>,
 *     publications: list<string>
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
     * @param  Localised  $values
     */
    public static function text(array $values, ?string $locale = null): string
    {
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
     * Two-letter monogram for people without a photo.
     */
    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = array_map(fn (string $part) => mb_substr($part, 0, 1), array_slice($parts, 0, 2));

        return mb_strtoupper(implode('', $letters));
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
            'since' => null,
            'about' => [],
            'languages' => ['ru' => 'Узбекский, русский, английский', 'en' => 'Uzbek, Russian, English'],
            'interests' => [],
            'education' => [],
            'experience' => [],
            'publications' => [],
        ];
        $row['slug'] ??= Str::slug($row['name']['en']);
        $row['email'] ??= Str::lower(Str::of($row['name']['en'])->explode(' ')->map(fn ($p, $i) => $i === 0 ? mb_substr($p, 0, 1) : $p)->implode('.')).'@cerr.uz';

        return $row;
    }

    /** @var list<array<string, mixed>> */
    private const DATA = [
        // ---- Macroeconomic policy ------------------------------------------------
        [
            'sector' => 'macro', 'is_head' => true, 'position' => 'head', 'degree' => 'doc', 'since' => 2014,
            'name' => ['ru' => 'Абдуллаев Бахтиёр Рустамович', 'en' => 'Bakhtiyor Abdullaev'],
            'phone' => '+998 78 150-02-02',
            'about' => [
                'ru' => 'Руководит исследованиями согласованности бюджетной и денежно-кредитной политики, отвечает за среднесрочный макроэкономический прогноз Центра.',
                'en' => 'Leads research on the consistency of fiscal and monetary policy and is responsible for the Center’s medium-term macroeconomic forecast.',
            ],
            'interests' => [
                'ru' => ['Макроэкономическое прогнозирование', 'Бюджетная политика', 'Инфляционное таргетирование', 'DSGE-модели'],
                'en' => ['Macroeconomic forecasting', 'Fiscal policy', 'Inflation targeting', 'DSGE models'],
            ],
            'education' => [
                ['years' => '2009–2012', 'text' => ['ru' => 'Докторантура, Ташкентский государственный экономический университет — доктор экономических наук', 'en' => 'Doctoral programme, Tashkent State University of Economics — Doctor of Economics']],
                ['years' => '2003–2005', 'text' => ['ru' => 'Магистратура, Университет мировой экономики и дипломатии — макроэкономика', 'en' => 'MSc, University of World Economy and Diplomacy — Macroeconomics']],
            ],
            'experience' => [
                ['years' => '2019 — н. в.', 'text' => ['ru' => 'Заведующий сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2014–2019', 'text' => ['ru' => 'Ведущий научный сотрудник, ЦЭИР', 'en' => 'Leading Researcher, CERR']],
                ['years' => '2008–2014', 'text' => ['ru' => 'Главный специалист, Министерство экономики Республики Узбекистан', 'en' => 'Chief Specialist, Ministry of Economy of the Republic of Uzbekistan']],
            ],
            'publications' => [
                'Абдуллаев Б. Р. Согласованность бюджетной и денежно-кредитной политики в условиях перехода к инфляционному таргетированию // Экономическое обозрение. — 2024. — № 3. — С. 12–27.',
                'Abdullaev B., Karimova N. Fiscal multipliers in a small open economy: evidence from Uzbekistan // Central Asian Economic Review. — 2023. — Vol. 8 (2). — P. 45–68.',
                'Абдуллаев Б. Р. Среднесрочный прогноз макроэкономических параметров: методология ЦЭИР. — Ташкент: ЦЭИР, 2022. — 96 с.',
            ],
        ],
        [
            'sector' => 'macro', 'position' => 'leading', 'degree' => 'phd', 'since' => 2017,
            'name' => ['ru' => 'Каримова Нилуфар Шавкатовна', 'en' => 'Nilufar Karimova'],
            'about' => ['ru' => 'Специализируется на моделировании инфляции и оценке эффектов денежно-кредитной политики.', 'en' => 'Specialises in inflation modelling and the evaluation of monetary policy effects.'],
            'interests' => ['ru' => ['Инфляция', 'Трансмиссионный механизм', 'Эконометрика временных рядов'], 'en' => ['Inflation', 'Transmission mechanism', 'Time-series econometrics']],
            'education' => [['years' => '2013–2017', 'text' => ['ru' => 'PhD по экономике, Банковско-финансовая академия', 'en' => 'PhD in Economics, Banking and Finance Academy']]],
            'publications' => ['Karimova N. Exchange-rate pass-through to consumer prices in Uzbekistan // Journal of Asian Economics. — 2024. — Vol. 91.'],
        ],
        [
            'sector' => 'macro', 'position' => 'senior', 'since' => 2018,
            'name' => ['ru' => 'Юсупов Жасур Акмалович', 'en' => 'Jasur Yusupov'],
            'about' => ['ru' => 'Ведёт квартальную модель платёжного баланса и сценарные расчёты внешнего долга.', 'en' => 'Maintains the quarterly balance-of-payments model and external-debt scenario analysis.'],
            'interests' => ['ru' => ['Платёжный баланс', 'Внешний долг', 'Сценарное моделирование'], 'en' => ['Balance of payments', 'External debt', 'Scenario modelling']],
            'education' => [['years' => '2014–2016', 'text' => ['ru' => 'Магистратура, Ташкентский государственный экономический университет', 'en' => 'MSc, Tashkent State University of Economics']]],
        ],
        [
            'sector' => 'macro', 'position' => 'researcher', 'since' => 2021,
            'name' => ['ru' => 'Рахимова Дилноза Бахтиёровна', 'en' => 'Dilnoza Rakhimova'],
            'about' => ['ru' => 'Занимается оценкой потенциального ВВП и разрыва выпуска.', 'en' => 'Works on potential GDP and output-gap estimation.'],
            'interests' => ['ru' => ['Потенциальный ВВП', 'Фильтрация временных рядов'], 'en' => ['Potential GDP', 'Time-series filtering']],
            'education' => [['years' => '2019–2021', 'text' => ['ru' => 'Магистратура, Вестминстерский международный университет в Ташкенте', 'en' => 'MSc, Westminster International University in Tashkent']]],
        ],
        [
            'sector' => 'macro', 'position' => 'junior', 'since' => 2024,
            'name' => ['ru' => 'Тошматов Отабек Улугбекович', 'en' => 'Otabek Toshmatov'],
            'about' => ['ru' => 'Готовит ежемесячный мониторинг макроэкономических индикаторов.', 'en' => 'Prepares the monthly macroeconomic indicators monitor.'],
            'interests' => ['ru' => ['Макроэкономическая статистика', 'Визуализация данных'], 'en' => ['Macroeconomic statistics', 'Data visualisation']],
            'education' => [['years' => '2020–2024', 'text' => ['ru' => 'Бакалавриат, Университет мировой экономики и дипломатии', 'en' => 'BSc, University of World Economy and Diplomacy']]],
        ],

        // ---- Banking, finance and capital markets ---------------------------------
        [
            'sector' => 'bank', 'is_head' => true, 'position' => 'head', 'degree' => 'cand', 'since' => 2012,
            'name' => ['ru' => 'Мирзаев Шерзод Анварович', 'en' => 'Sherzod Mirzaev'],
            'phone' => '+998 78 150-02-02',
            'about' => ['ru' => 'Исследует развитие банковского сектора, рынка капитала и доступ бизнеса к финансированию.', 'en' => 'Researches banking-sector development, capital markets and business access to finance.'],
            'interests' => ['ru' => ['Банковское регулирование', 'Рынок капитала', 'Финансовая доступность', 'Финтех'], 'en' => ['Banking regulation', 'Capital markets', 'Financial inclusion', 'Fintech']],
            'education' => [
                ['years' => '2006–2009', 'text' => ['ru' => 'Аспирантура, Банковско-финансовая академия — кандидат экономических наук', 'en' => 'Postgraduate programme, Banking and Finance Academy — Candidate of Economic Sciences']],
            ],
            'experience' => [
                ['years' => '2017 — н. в.', 'text' => ['ru' => 'Заведующий сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2012–2017', 'text' => ['ru' => 'Старший научный сотрудник, ЦЭИР', 'en' => 'Senior Researcher, CERR']],
                ['years' => '2005–2012', 'text' => ['ru' => 'Начальник отдела, Центральный банк Республики Узбекистан', 'en' => 'Head of Division, Central Bank of the Republic of Uzbekistan']],
            ],
            'publications' => [
                'Мирзаев Ш. А. Трансформация банковского сектора Узбекистана: итоги приватизации // Экономическое обозрение. — 2025. — № 1.',
                'Mirzaev S., Nazarova G. Credit to SMEs and regional growth in Uzbekistan // Emerging Markets Finance and Trade. — 2023.',
            ],
        ],
        [
            'sector' => 'bank', 'position' => 'leading', 'degree' => 'phd', 'since' => 2016,
            'name' => ['ru' => 'Назарова Гульнора Тохировна', 'en' => 'Gulnora Nazarova'],
            'about' => ['ru' => 'Изучает развитие фондового рынка и инструментов долгового финансирования.', 'en' => 'Studies stock-market development and debt-financing instruments.'],
            'interests' => ['ru' => ['Фондовый рынок', 'Корпоративные облигации', 'IPO'], 'en' => ['Stock market', 'Corporate bonds', 'IPOs']],
            'education' => [['years' => '2012–2016', 'text' => ['ru' => 'PhD, Ташкентский финансовый институт', 'en' => 'PhD, Tashkent Institute of Finance']]],
            'publications' => ['Nazarova G. Determinants of IPO activity in frontier markets // Review of Development Finance. — 2024.'],
        ],
        [
            'sector' => 'bank', 'position' => 'senior', 'since' => 2019,
            'name' => ['ru' => 'Хакимов Фаррух Баходирович', 'en' => 'Farrukh Khakimov'],
            'about' => ['ru' => 'Анализирует кредитование малого бизнеса и процентные ставки.', 'en' => 'Analyses small-business lending and interest-rate dynamics.'],
            'interests' => ['ru' => ['Кредитование МСБ', 'Процентная политика'], 'en' => ['SME lending', 'Interest-rate policy']],
            'education' => [['years' => '2015–2017', 'text' => ['ru' => 'Магистратура, Банковско-финансовая академия', 'en' => 'MSc, Banking and Finance Academy']]],
        ],
        [
            'sector' => 'bank', 'position' => 'researcher', 'since' => 2022,
            'name' => ['ru' => 'Исмоилова Мадина Рустамовна', 'en' => 'Madina Ismoilova'],
            'about' => ['ru' => 'Готовит обзоры по цифровым платежам и финтех-сервисам.', 'en' => 'Prepares reviews of digital payments and fintech services.'],
            'interests' => ['ru' => ['Цифровые платежи', 'Финтех'], 'en' => ['Digital payments', 'Fintech']],
            'education' => [['years' => '2020–2022', 'text' => ['ru' => 'Магистратура, Ташкентский финансовый институт', 'en' => 'MSc, Tashkent Institute of Finance']]],
        ],

        // ---- Competitiveness and investment ----------------------------------------
        [
            'sector' => 'competitiveness', 'is_head' => true, 'position' => 'head', 'degree' => 'phd', 'since' => 2015,
            'name' => ['ru' => 'Салимов Жамшид Каримович', 'en' => 'Jamshid Salimov'],
            'phone' => '+998 78 150-02-02',
            'about' => ['ru' => 'Отвечает за оценку конкурентоспособности отраслей, индекс деловой активности и анализ инвестиционного климата.', 'en' => 'Responsible for sectoral competitiveness assessment, the business activity index and investment-climate analysis.'],
            'interests' => ['ru' => ['Промышленная политика', 'Производительность', 'Инвестиционный климат', 'Индексы деловой активности'], 'en' => ['Industrial policy', 'Productivity', 'Investment climate', 'Business activity indices']],
            'education' => [['years' => '2010–2014', 'text' => ['ru' => 'PhD по экономике, Университет Цукубы (Япония)', 'en' => 'PhD in Economics, University of Tsukuba (Japan)']]],
            'experience' => [
                ['years' => '2020 — н. в.', 'text' => ['ru' => 'Заведующий сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2015–2020', 'text' => ['ru' => 'Ведущий научный сотрудник, ЦЭИР', 'en' => 'Leading Researcher, CERR']],
            ],
            'publications' => [
                'Салимов Ж. К. Индекс деловой активности: методология и результаты 2024 года. — Ташкент: ЦЭИР, 2025.',
                'Salimov J. Total factor productivity in Uzbek manufacturing, 2010–2022 // Post-Communist Economies. — 2024.',
            ],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'senior', 'degree' => 'phd', 'since' => 2018,
            'name' => ['ru' => 'Эргашева Малика Алишеровна', 'en' => 'Malika Ergasheva'],
            'about' => ['ru' => 'Исследует производительность и технологическое обновление в обрабатывающей промышленности.', 'en' => 'Researches productivity and technological upgrading in manufacturing.'],
            'interests' => ['ru' => ['Производительность труда', 'Технологии', 'Обрабатывающая промышленность'], 'en' => ['Labour productivity', 'Technology', 'Manufacturing']],
            'education' => [['years' => '2014–2018', 'text' => ['ru' => 'PhD, Ташкентский государственный экономический университет', 'en' => 'PhD, Tashkent State University of Economics']]],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'researcher', 'since' => 2020,
            'name' => ['ru' => 'Умаров Давлат Шухратович', 'en' => 'Davlat Umarov'],
            'about' => ['ru' => 'Ведёт мониторинг прямых иностранных инвестиций по отраслям.', 'en' => 'Monitors foreign direct investment by industry.'],
            'interests' => ['ru' => ['Прямые иностранные инвестиции', 'Отраслевой анализ'], 'en' => ['Foreign direct investment', 'Industry analysis']],
            'education' => [['years' => '2018–2020', 'text' => ['ru' => 'Магистратура, Университет мировой экономики и дипломатии', 'en' => 'MSc, University of World Economy and Diplomacy']]],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'researcher', 'since' => 2021,
            'name' => ['ru' => 'Азизова Севара Фарходовна', 'en' => 'Sevara Azizova'],
            'about' => ['ru' => 'Проводит опросы предприятий для индекса деловой активности.', 'en' => 'Runs the enterprise surveys behind the business activity index.'],
            'interests' => ['ru' => ['Опросы предприятий', 'Деловой климат'], 'en' => ['Enterprise surveys', 'Business climate']],
            'education' => [['years' => '2019–2021', 'text' => ['ru' => 'Магистратура, Ташкентский государственный экономический университет', 'en' => 'MSc, Tashkent State University of Economics']]],
        ],
        [
            'sector' => 'competitiveness', 'position' => 'junior', 'since' => 2023,
            'name' => ['ru' => 'Нурматов Бекзод Икромович', 'en' => 'Bekzod Nurmatov'],
            'about' => ['ru' => 'Собирает и обрабатывает данные по регистрации новых предприятий.', 'en' => 'Collects and processes new-business registration data.'],
            'interests' => ['ru' => ['Предпринимательство', 'Обработка данных'], 'en' => ['Entrepreneurship', 'Data processing']],
            'education' => [['years' => '2019–2023', 'text' => ['ru' => 'Бакалавриат, Ташкентский государственный экономический университет', 'en' => 'BSc, Tashkent State University of Economics']]],
        ],

        // ---- Foreign economic activity and integration -----------------------------
        [
            'sector' => 'foreign_economic', 'is_head' => true, 'position' => 'head', 'degree' => 'doc', 'since' => 2013,
            'name' => ['ru' => 'Ахмедова Наргиза Абдуллаевна', 'en' => 'Nargiza Akhmedova'],
            'phone' => '+998 78 150-02-02',
            'about' => ['ru' => 'Возглавляет исследования по внешней торговле, вступлению в ВТО и региональной интеграции в Центральной Азии.', 'en' => 'Heads research on foreign trade, WTO accession and regional integration in Central Asia.'],
            'interests' => ['ru' => ['Внешняя торговля', 'ВТО', 'Региональная интеграция', 'Торговая политика'], 'en' => ['Foreign trade', 'WTO', 'Regional integration', 'Trade policy']],
            'education' => [
                ['years' => '2011–2014', 'text' => ['ru' => 'Докторантура, Университет мировой экономики и дипломатии — доктор экономических наук', 'en' => 'Doctoral programme, University of World Economy and Diplomacy — Doctor of Economics']],
            ],
            'experience' => [
                ['years' => '2018 — н. в.', 'text' => ['ru' => 'Заведующая сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2013–2018', 'text' => ['ru' => 'Главный научный сотрудник, ЦЭИР', 'en' => 'Chief Researcher, CERR']],
            ],
            'publications' => [
                'Ахмедова Н. А. Эффекты вступления Узбекистана в ВТО: оценка на основе модели общего равновесия // Экономическое обозрение. — 2024. — № 6.',
                'Akhmedova N., Sobirov U. Trade facilitation and export diversification in Central Asia // World Economy. — 2023.',
            ],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'leading', 'degree' => 'phd', 'since' => 2016,
            'name' => ['ru' => 'Собиров Улугбек Шерзодович', 'en' => 'Ulugbek Sobirov'],
            'about' => ['ru' => 'Моделирует эффекты торговых соглашений с помощью гравитационных моделей.', 'en' => 'Models the effects of trade agreements with gravity models.'],
            'interests' => ['ru' => ['Гравитационные модели', 'Торговые соглашения', 'Экспортная диверсификация'], 'en' => ['Gravity models', 'Trade agreements', 'Export diversification']],
            'education' => [['years' => '2012–2016', 'text' => ['ru' => 'PhD, Университет мировой экономики и дипломатии', 'en' => 'PhD, University of World Economy and Diplomacy']]],
            'publications' => ['Sobirov U. Non-tariff measures and Uzbek exports: a gravity approach // Eurasian Economic Review. — 2024.'],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'senior', 'since' => 2019,
            'name' => ['ru' => 'Жураева Зилола Нодировна', 'en' => 'Zilola Juraeva'],
            'about' => ['ru' => 'Исследует транспортные коридоры и логистические издержки экспорта.', 'en' => 'Researches transport corridors and the logistics costs of exporting.'],
            'interests' => ['ru' => ['Транспортные коридоры', 'Логистика', 'Транзит'], 'en' => ['Transport corridors', 'Logistics', 'Transit']],
            'education' => [['years' => '2015–2017', 'text' => ['ru' => 'Магистратура, Ташкентский государственный транспортный университет', 'en' => 'MSc, Tashkent State Transport University']]],
        ],
        [
            'sector' => 'foreign_economic', 'position' => 'researcher', 'since' => 2022,
            'name' => ['ru' => 'Кодиров Санжар Муродович', 'en' => 'Sanjar Kodirov'],
            'about' => ['ru' => 'Ведёт базу данных двусторонней торговли со странами ЕАЭС и Китаем.', 'en' => 'Maintains the bilateral trade database for EAEU countries and China.'],
            'interests' => ['ru' => ['Торговая статистика', 'ЕАЭС', 'Китай'], 'en' => ['Trade statistics', 'EAEU', 'China']],
            'education' => [['years' => '2020–2022', 'text' => ['ru' => 'Магистратура, Университет мировой экономики и дипломатии', 'en' => 'MSc, University of World Economy and Diplomacy']]],
        ],

        // ---- Industrial clusters ----------------------------------------------------
        [
            'sector' => 'cluster', 'is_head' => true, 'position' => 'head', 'degree' => 'cand', 'since' => 2016,
            'name' => ['ru' => 'Ташкентов Аброр Хамидович', 'en' => 'Abror Tashkentov'],
            'phone' => '+998 78 150-02-02',
            'about' => ['ru' => 'Руководит разработкой методологии развития промышленных и агрокластеров и оценкой их эффективности.', 'en' => 'Leads the methodology for developing industrial and agro-clusters and evaluating their performance.'],
            'interests' => ['ru' => ['Кластерная политика', 'Агрокластеры', 'Цепочки добавленной стоимости'], 'en' => ['Cluster policy', 'Agro-clusters', 'Value chains']],
            'education' => [['years' => '2008–2011', 'text' => ['ru' => 'Аспирантура, Ташкентский государственный экономический университет — кандидат экономических наук', 'en' => 'Postgraduate programme, Tashkent State University of Economics — Candidate of Economic Sciences']]],
            'experience' => [
                ['years' => '2021 — н. в.', 'text' => ['ru' => 'Заведующий сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2016–2021', 'text' => ['ru' => 'Старший научный сотрудник, ЦЭИР', 'en' => 'Senior Researcher, CERR']],
            ],
            'publications' => ['Ташкентов А. Х. Хлопково-текстильные кластеры: первые итоги реформы // Экономическое обозрение. — 2023. — № 9.'],
        ],
        [
            'sector' => 'cluster', 'position' => 'senior', 'since' => 2018,
            'name' => ['ru' => 'Холматова Шахноза Равшановна', 'en' => 'Shakhnoza Kholmatova'],
            'about' => ['ru' => 'Анализирует текстильные и кожевенные кластеры и их экспортный потенциал.', 'en' => 'Analyses textile and leather clusters and their export potential.'],
            'interests' => ['ru' => ['Текстильная промышленность', 'Экспорт', 'Кластеры'], 'en' => ['Textile industry', 'Exports', 'Clusters']],
            'education' => [['years' => '2014–2016', 'text' => ['ru' => 'Магистратура, Ташкентский институт текстильной и лёгкой промышленности', 'en' => 'MSc, Tashkent Institute of Textile and Light Industry']]],
        ],
        [
            'sector' => 'cluster', 'position' => 'researcher', 'since' => 2021,
            'name' => ['ru' => 'Бобоев Комил Турсунович', 'en' => 'Komil Boboev'],
            'about' => ['ru' => 'Исследует эффективность агрокластеров в плодоовощной отрасли.', 'en' => 'Researches the performance of agro-clusters in horticulture.'],
            'interests' => ['ru' => ['Агрокластеры', 'Плодоовощеводство'], 'en' => ['Agro-clusters', 'Horticulture']],
            'education' => [['years' => '2019–2021', 'text' => ['ru' => 'Магистратура, Ташкентский государственный аграрный университет', 'en' => 'MSc, Tashkent State Agrarian University']]],
        ],

        // ---- Regional development ---------------------------------------------------
        [
            'sector' => 'regions', 'is_head' => true, 'position' => 'head', 'degree' => 'phd', 'since' => 2015,
            'name' => ['ru' => 'Мамадалиев Санжарбек Мухтарович', 'en' => 'Sanjarbek Mamadaliev'],
            'phone' => '+998 78 150-02-02',
            'about' => ['ru' => 'Координирует исследования социально-экономического развития регионов и работу региональных филиалов Центра.', 'en' => 'Coordinates research on regional socio-economic development and the work of the Center’s regional branches.'],
            'interests' => ['ru' => ['Региональная экономика', 'Межбюджетные отношения', 'Урбанизация', 'Рынок труда'], 'en' => ['Regional economics', 'Intergovernmental finance', 'Urbanisation', 'Labour market']],
            'education' => [['years' => '2011–2015', 'text' => ['ru' => 'PhD по экономике, Университет Хитоцубаси (Япония)', 'en' => 'PhD in Economics, Hitotsubashi University (Japan)']]],
            'experience' => [
                ['years' => '2019 — н. в.', 'text' => ['ru' => 'Заведующий сектором, ЦЭИР', 'en' => 'Head of Sector, CERR']],
                ['years' => '2015–2019', 'text' => ['ru' => 'Ведущий научный сотрудник, ЦЭИР', 'en' => 'Leading Researcher, CERR']],
            ],
            'publications' => [
                'Мамадалиев С. М. Региональные диспропорции и бюджетное выравнивание в Узбекистане // Экономическое обозрение. — 2025. — № 2.',
                'Mamadaliev S., Rasulova F. Urban agglomerations and productivity in Uzbekistan // Regional Studies. — 2024.',
            ],
        ],
        [
            'sector' => 'regions', 'position' => 'leading', 'degree' => 'phd', 'since' => 2017,
            'name' => ['ru' => 'Расулова Феруза Хасановна', 'en' => 'Feruza Rasulova'],
            'about' => ['ru' => 'Изучает рынок труда и миграцию в разрезе регионов.', 'en' => 'Studies regional labour markets and migration.'],
            'interests' => ['ru' => ['Рынок труда', 'Трудовая миграция', 'Занятость'], 'en' => ['Labour market', 'Labour migration', 'Employment']],
            'education' => [['years' => '2013–2017', 'text' => ['ru' => 'PhD, Ташкентский государственный экономический университет', 'en' => 'PhD, Tashkent State University of Economics']]],
            'publications' => ['Rasulova F. Remittances and household welfare across Uzbek regions // Journal of Development Studies. — 2023.'],
        ],
        [
            'sector' => 'regions', 'position' => 'senior', 'since' => 2019,
            'name' => ['ru' => 'Турсунов Шохрух Бахромович', 'en' => 'Shokhrukh Tursunov'],
            'about' => ['ru' => 'Ведёт рейтинг социально-экономического развития районов и городов.', 'en' => 'Maintains the socio-economic development ranking of districts and cities.'],
            'interests' => ['ru' => ['Рейтинги регионов', 'Композитные индексы'], 'en' => ['Regional rankings', 'Composite indices']],
            'education' => [['years' => '2015–2017', 'text' => ['ru' => 'Магистратура, Ташкентский государственный экономический университет', 'en' => 'MSc, Tashkent State University of Economics']]],
        ],
        [
            'sector' => 'regions', 'position' => 'researcher', 'since' => 2022,
            'name' => ['ru' => 'Хасанова Муниса Азаматовна', 'en' => 'Munisa Khasanova'],
            'about' => ['ru' => 'Исследует бедность и социальную защиту в сельских районах.', 'en' => 'Researches poverty and social protection in rural districts.'],
            'interests' => ['ru' => ['Бедность', 'Социальная защита', 'Сельские территории'], 'en' => ['Poverty', 'Social protection', 'Rural areas']],
            'education' => [['years' => '2020–2022', 'text' => ['ru' => 'Магистратура, Национальный университет Узбекистана', 'en' => 'MSc, National University of Uzbekistan']]],
        ],
        [
            'sector' => 'regions', 'position' => 'junior', 'since' => 2024,
            'name' => ['ru' => 'Эшонкулов Жавохир Собирович', 'en' => 'Javokhir Eshonkulov'],
            'about' => ['ru' => 'Обрабатывает муниципальную статистику для региональных обзоров.', 'en' => 'Processes municipal statistics for the regional reviews.'],
            'interests' => ['ru' => ['Муниципальная статистика', 'ГИС'], 'en' => ['Municipal statistics', 'GIS']],
            'education' => [['years' => '2020–2024', 'text' => ['ru' => 'Бакалавриат, Национальный университет Узбекистана', 'en' => 'BSc, National University of Uzbekistan']]],
        ],
    ];
}
