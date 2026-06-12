<?php

describe('Admin Blade components', function () {
    beforeEach(function () {
        app()->setLocale('ru');
    });

    it('renders a translated status pill', function () {
        $html = $this->blade('<x-admin.status-pill status="published" />');

        $html->assertSee('Опубликовано');
        $html->assertSee('status-published', false);
    });

    it('falls back to the raw value for unknown statuses', function () {
        $html = $this->blade('<x-admin.status-pill status="bogus" />');

        $html->assertSee('bogus');
        $html->assertSee('status-bogus', false);
    });

    it('renders lang chips marking missing locales', function () {
        $html = $this->blade('<x-admin.lang-chips :available="[\'ru\', \'uz\']" />');

        $html->assertSeeInOrder(['kr', 'uz', 'ru', 'en']);
        $html->assertSee('lang-chip missing', false);
    });

    it('renders a stat card with accent', function () {
        $html = $this->blade('<x-admin.stat-card label="Черновики" value="3" icon="fa-regular fa-pen-to-square" accent="warning" />');

        $html->assertSee('Черновики');
        $html->assertSee('3');
        $html->assertSee('accent-warning', false);
        $html->assertSee('stat-icon', false);
    });

    it('renders a page header with actions slot', function () {
        $html = $this->blade('<x-admin.page-header title="Новости" subtitle="Управление"><button>Создать</button></x-admin.page-header>');

        $html->assertSee('Новости');
        $html->assertSee('Управление');
        $html->assertSee('Создать');
    });

    it('renders an empty state', function () {
        $html = $this->blade('<x-admin.empty-state icon="fa-regular fa-newspaper" title="Пусто" />');

        $html->assertSee('Пусто');
        $html->assertSee('fa-regular fa-newspaper', false);
    });
});
