<div>
    @push('styles')
        <style>
            /* The theme sets overflow-x:hidden on html/body, which disables position:sticky.
               'clip' blocks horizontal scroll the same way but keeps the sticky rail working. */
            html { overflow-x: clip !important; overflow-y: visible !important; }
            body { overflow-x: clip !important; }
            .od-catalog { font-family: 'Source Sans 3', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #f7f8fc; color: #1c2240; padding: 46px 0 66px; line-height: 1.5; -webkit-font-smoothing: antialiased; }
            .od-catalog *, .od-catalog *::before, .od-catalog *::after { box-sizing: border-box; }
            .od-catalog .od-wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
            .od-catalog .od-head { margin-bottom: 24px; }
            .od-catalog .od-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #3a4c7a; margin: 0 0 6px; }
            .od-catalog .od-title { font-size: 28px; font-weight: 800; letter-spacing: -.01em; margin: 0; color: #1c2240; }
            .od-catalog .od-sub { font-size: 14.5px; color: #8a8fa3; margin: 7px 0 0; max-width: 620px; }
            .od-catalog .od-grid { display: grid; grid-template-columns: 264px 1fr; gap: 24px; align-items: start; }

            .od-catalog .od-rail { background: #fff; border: 1px solid #ecedf3; border-radius: 16px; padding: 18px; box-shadow: 0 6px 22px rgba(28,34,64,.05); position: sticky; top: 100px; }
            .od-catalog .od-search { position: relative; margin-bottom: 20px; }
            .od-catalog .od-search svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8a8fa3; pointer-events: none; }
            .od-catalog .od-search input { width: 100%; height: 42px; border: 1px solid #ecedf3; border-radius: 11px; background: #f7f8fc; padding: 0 12px 0 38px; font-size: 13.5px; color: #1c2240; font-family: inherit; outline: none; transition: border-color .15s, box-shadow .15s, background .15s; }
            .od-catalog .od-search input::placeholder { color: #8a8fa3; }
            .od-catalog .od-search input:focus { border-color: #3a4c7a; background: #fff; box-shadow: 0 0 0 3px rgba(58,76,122,.12); }

            .od-catalog .od-facet { margin-bottom: 20px; }
            .od-catalog .od-facet-label { font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #8a8fa3; margin: 0 0 10px; }
            .od-catalog .od-years { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 2px; }
            .od-catalog .od-year { display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 9px 12px; border: none; background: none; font-family: inherit; border-radius: 10px; font-size: 14px; font-weight: 500; color: #1c2240; text-align: left; position: relative; transition: background .15s, color .15s; cursor: pointer; }
            .od-catalog .od-year .od-count { font-size: 12px; font-weight: 600; color: #8a8fa3; background: #f1f2f8; border-radius: 20px; padding: 2px 9px; min-width: 30px; text-align: center; transition: background .15s, color .15s; }
            .od-catalog .od-year:hover { background: #f7f8fc; }
            .od-catalog .od-year.is-active { background: #eef1f8; color: #3a4c7a; font-weight: 700; }
            .od-catalog .od-year.is-active::before { content: ""; position: absolute; left: 0; top: 7px; bottom: 7px; width: 3px; border-radius: 3px; background: #3a4c7a; }
            .od-catalog .od-year.is-active .od-count { background: #3a4c7a; color: #fff; }

            .od-catalog .od-seg { display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px; }
            .od-catalog .od-seg-all { grid-column: 1 / -1; }
            .od-catalog .od-chip { display: flex; align-items: center; justify-content: center; height: 38px; border: 1px solid #ecedf3; border-radius: 10px; background: #fff; font-family: inherit; font-size: 13px; font-weight: 600; color: #1c2240; cursor: pointer; transition: all .15s; }
            .od-catalog .od-chip:hover { border-color: #c7cde0; }
            .od-catalog .od-chip.is-active { background: #3a4c7a; border-color: #3a4c7a; color: #fff; box-shadow: 0 4px 12px rgba(58,76,122,.25); }

            .od-catalog .od-clear { margin-top: 16px; padding-top: 16px; border-top: 1px solid #ecedf3; }
            .od-catalog .od-clear button { border: none; background: none; font-family: inherit; font-size: 13px; color: #8a8fa3; cursor: pointer; font-weight: 500; padding: 0; }
            .od-catalog .od-clear button:hover { color: #3a4c7a; }

            .od-catalog .od-main { min-width: 0; }
            .od-catalog .od-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 0 4px; flex-wrap: wrap; gap: 10px; }
            .od-catalog .od-found { font-size: 14.5px; color: #1c2240; }
            .od-catalog .od-found b { font-weight: 700; }
            .od-catalog .od-sort { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #8a8fa3; }
            .od-catalog .od-sort select { appearance: none; -webkit-appearance: none; font-family: inherit; font-size: 13px; font-weight: 600; color: #1c2240; background-color: #fff; border: 1px solid #ecedf3; border-radius: 9px; padding: 7px 32px 7px 12px; cursor: pointer; outline: none; transition: border-color .15s, box-shadow .15s;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 11px center; }
            .od-catalog .od-sort select:focus { border-color: #3a4c7a; box-shadow: 0 0 0 3px rgba(58,76,122,.12); }

            .od-catalog .od-list { display: flex; flex-direction: column; gap: 11px; transition: opacity .15s; }
            .od-catalog .od-list.is-loading { opacity: .5; pointer-events: none; }
            .od-catalog .od-card { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid #ecedf3; border-radius: 14px; padding: 16px 18px; box-shadow: 0 3px 14px rgba(28,34,64,.04); transition: transform .15s, box-shadow .15s, border-color .15s; }
            .od-catalog .od-card:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(28,34,64,.1); border-color: #dfe2ee; }
            .od-catalog .od-badge { flex: none; display: inline-flex; align-items: center; justify-content: center; min-width: 52px; height: 26px; padding: 0 9px; border-radius: 7px; font-size: 11px; font-weight: 800; letter-spacing: .04em; color: #fff; }
            .od-catalog .od-badge.pdf { background: #d8362a; }
            .od-catalog .od-badge.xlsx, .od-catalog .od-badge.csv { background: #159d57; }
            .od-catalog .od-badge.docx { background: #2b6fd1; }
            .od-catalog .od-badge.file { background: #3a4c7a; }
            .od-catalog .od-body { flex: 1 1 auto; min-width: 0; }
            .od-catalog .od-name { font-size: 15.5px; font-weight: 600; color: #1c2240; margin: 0 0 5px; line-height: 1.35; }
            .od-catalog .od-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 12.5px; color: #8a8fa3; }
            .od-catalog .od-meta .dot { width: 3px; height: 3px; border-radius: 50%; background: #c7cde0; }
            .od-catalog .od-meta .yq { color: #3a4c7a; font-weight: 600; }
            .od-catalog .od-dl-meta { display: inline-flex; align-items: center; gap: 4px; }
            .od-catalog .od-dl { flex: none; display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 16px; border-radius: 10px; background: #3a4c7a; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; box-shadow: 0 4px 12px rgba(58,76,122,.22); transition: background .15s, transform .15s; }
            .od-catalog .od-dl:hover { background: #2f3e66; color: #fff; transform: translateY(-1px); }
            .od-catalog .od-dl:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(58,76,122,.35); }

            .od-catalog .od-empty { text-align: center; padding: 70px 20px; color: #9aa0b4; background: #fff; border: 1px solid #ecedf3; border-radius: 14px; }
            .od-catalog .od-empty svg { margin-bottom: 12px; opacity: .5; }
            .od-catalog .od-empty p { margin: 0; font-size: 15px; }

            .od-catalog .od-pager { display: flex; flex-flow: row nowrap; justify-content: center; align-items: center; gap: 6px; margin-top: 28px; }
            .od-catalog .od-page { flex: 0 0 auto; width: auto; display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 6px; border-radius: 9px; font-family: inherit; font-size: 13.5px; font-weight: 600; color: #1c2240; line-height: 1; text-decoration: none; border: 1px solid transparent; background: none; cursor: pointer; transition: background .15s, border-color .15s, color .15s; }
            .od-catalog .od-ellipsis { flex: 0 0 auto; }
            .od-catalog .od-page:hover { background: #fff; border-color: #ecedf3; }
            .od-catalog .od-page.is-current { background: #3a4c7a; color: #fff; box-shadow: 0 4px 12px rgba(58,76,122,.22); }
            .od-catalog .od-page.od-nav { color: #8a8fa3; }
            .od-catalog .od-page.od-nav:hover { color: #3a4c7a; }
            .od-catalog .od-page.is-disabled { color: #c7cde0; cursor: default; }
            .od-catalog .od-page.is-disabled:hover { background: none; border-color: transparent; }
            .od-catalog .od-ellipsis { display: inline-flex; align-items: flex-end; justify-content: center; min-width: 26px; height: 36px; color: #8a8fa3; font-weight: 700; padding-bottom: 6px; }

            @media (max-width: 767px) {
                .od-catalog .od-grid { grid-template-columns: 1fr; }
                .od-catalog .od-rail { position: static; }
                .od-catalog .od-card { flex-wrap: wrap; }
                .od-catalog .od-dl { width: 100%; justify-content: center; }
                .od-catalog .od-pager { justify-content: center; }
            }

            /* ---- dark mode ---- */
            [data-theme="dark"] .od-catalog { background: #0f0f17; color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-title { color: #f2f3f8; }
            [data-theme="dark"] .od-catalog .od-rail { background: #1b1b25; border-color: rgba(255,255,255,.07); box-shadow: 0 6px 22px rgba(0,0,0,.3); }
            [data-theme="dark"] .od-catalog .od-search input { background: #14141d; border-color: rgba(255,255,255,.1); color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-search input:focus { background: #14141d; border-color: #6b7bb0; box-shadow: 0 0 0 3px rgba(107,123,176,.2); }
            [data-theme="dark"] .od-catalog .od-year { color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-year .od-count { background: rgba(255,255,255,.08); color: rgba(255,255,255,.6); }
            [data-theme="dark"] .od-catalog .od-year:hover { background: rgba(255,255,255,.04); }
            [data-theme="dark"] .od-catalog .od-year.is-active { background: rgba(110,123,176,.18); color: #aeb8de; }
            [data-theme="dark"] .od-catalog .od-year.is-active::before { background: #aeb8de; }
            [data-theme="dark"] .od-catalog .od-year.is-active .od-count { background: #6b7bb0; color: #fff; }
            [data-theme="dark"] .od-catalog .od-chip { background: #14141d; border-color: rgba(255,255,255,.1); color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-chip.is-active { background: #6b7bb0; border-color: #6b7bb0; color: #fff; }
            [data-theme="dark"] .od-catalog .od-clear { border-color: rgba(255,255,255,.08); }
            [data-theme="dark"] .od-catalog .od-found { color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-sort select { background-color: #1b1b25; border-color: rgba(255,255,255,.1); color: #e8eaf2;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aeb8de' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); }
            [data-theme="dark"] .od-catalog .od-card { background: #1b1b25; border-color: rgba(255,255,255,.07); box-shadow: 0 3px 14px rgba(0,0,0,.25); }
            [data-theme="dark"] .od-catalog .od-card:hover { border-color: rgba(255,255,255,.14); box-shadow: 0 10px 26px rgba(0,0,0,.4); }
            [data-theme="dark"] .od-catalog .od-name { color: #f2f3f8; }
            [data-theme="dark"] .od-catalog .od-meta .yq { color: #aeb8de; }
            [data-theme="dark"] .od-catalog .od-dl { background: #5a6aa0; }
            [data-theme="dark"] .od-catalog .od-dl:hover { background: #6b7bb0; color: #fff; }
            [data-theme="dark"] .od-catalog .od-empty { background: #1b1b25; border-color: rgba(255,255,255,.07); color: rgba(255,255,255,.6); }
            [data-theme="dark"] .od-catalog .od-page { color: #e8eaf2; }
            [data-theme="dark"] .od-catalog .od-page:hover { background: #1b1b25; border-color: rgba(255,255,255,.1); }
            [data-theme="dark"] .od-catalog .od-page.is-current { background: #6b7bb0; }
        </style>
    @endpush

    <section class="od-catalog">
        <div class="od-wrap">
            <div class="od-head">
                <p class="od-eyebrow">CERR Uzbekistan</p>
                <h1 class="od-title">@lang('messages.open_data')</h1>
            </div>

            <div class="od-grid">
                <aside class="od-rail">
                    <div class="od-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                        <input type="text" wire:model.live.debounce.350ms="search" placeholder="@lang('messages.open_data_search')" aria-label="@lang('messages.open_data_search')">
                    </div>

                    <div class="od-facet">
                        <p class="od-facet-label">@lang('messages.open_data_year')</p>
                        <ul class="od-years">
                            <li>
                                <button type="button" wire:click="$set('year', '')" class="od-year {{ $year === '' ? 'is-active' : '' }}">
                                    @lang('messages.open_data_years_all') <span class="od-count">{{ $yearCounts->sum() }}</span>
                                </button>
                            </li>
                            @foreach ($years as $y)
                                <li>
                                    <button type="button" wire:click="$set('year', '{{ $y }}')" class="od-year {{ (string) $year === (string) $y ? 'is-active' : '' }}">
                                        {{ $y }} <span class="od-count">{{ $yearCounts[$y] ?? 0 }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="od-facet">
                        <p class="od-facet-label">@lang('messages.open_data_quarter_label')</p>
                        <div class="od-seg">
                            <button type="button" wire:click="$set('quarter', '')" class="od-chip od-seg-all {{ $quarter === '' ? 'is-active' : '' }}">@lang('messages.open_data_quarters_all')</button>
                            @foreach ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $val => $label)
                                <button type="button" wire:click="$set('quarter', '{{ $val }}')" class="od-chip {{ (string) $quarter === (string) $val ? 'is-active' : '' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="od-clear">
                        <button type="button" wire:click="resetFilters">@lang('messages.open_data_reset')</button>
                    </div>
                </aside>

                <main class="od-main">
                    <div class="od-bar">
                        <div class="od-found"><b>@lang('messages.open_data_found') {{ $entries->total() }}</b></div>
                        <div class="od-sort">
                            <label for="od-sort">@lang('messages.open_data_sort'):</label>
                            <select id="od-sort" wire:model.live="sort">
                                <option value="new">@lang('messages.open_data_sort_new')</option>
                                <option value="old">@lang('messages.open_data_sort_old')</option>
                                <option value="popular">@lang('messages.open_data_sort_popular')</option>
                            </select>
                        </div>
                    </div>

                    <div class="od-list" wire:loading.class="is-loading" wire:target="search,year,quarter,sort,resetFilters,gotoPage,nextPage,previousPage">
                        @forelse ($entries as $entry)
                            @php
                                $ext = strtolower($entry->fileExtension());
                                $badge = match ($ext) {
                                    'pdf' => 'pdf',
                                    'xls', 'xlsx' => 'xlsx',
                                    'csv' => 'csv',
                                    'doc', 'docx' => 'docx',
                                    default => 'file',
                                };
                                $title = $entry->title() ?: '#'.$entry->id;
                            @endphp
                            <article class="od-card" wire:key="od-{{ $entry->id }}">
                                <span class="od-badge {{ $badge }}">{{ strtoupper($ext) ?: 'FILE' }}</span>
                                <div class="od-body">
                                    <p class="od-name">{{ $title }}</p>
                                    <div class="od-meta">
                                        <span class="yq">{{ $entry->year }}@if ($entry->quarter) · {{ $entry->quarterLabel() }} @lang('messages.open_data_quarter')@endif</span>
                                        @if ($ext)
                                            <span class="dot"></span><span>{{ strtoupper($ext) }}</span>
                                        @endif
                                        <span class="dot"></span><span>{{ $entry->fileSizeForHumans() }}</span>
                                        <span class="dot"></span>
                                        <span class="od-dl-meta">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 11l5 5 5-5"></path><path d="M5 21h14"></path></svg>
                                            {{ $entry->download_count }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ $entry->downloadUrl() }}" class="od-dl" aria-label="{{ $title }} — @lang('messages.open_data_download')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"></path><path d="M7 11l5 5 5-5"></path><path d="M5 21h14"></path></svg>
                                    @lang('messages.open_data_download')
                                </a>
                            </article>
                        @empty
                            <div class="od-empty">
                                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                <p>{{ __('messages.open_data_empty') }}</p>
                            </div>
                        @endforelse
                    </div>

                    {{ $entries->links('livewire.open-data.od-pager') }}
                </main>
            </div>
        </div>
    </section>
</div>
