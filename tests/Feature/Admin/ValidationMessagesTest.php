<?php

use Illuminate\Support\Facades\Validator;

describe('Admin validation messages', function () {
    it('translates validation messages to Russian with readable attribute names', function () {
        app()->setLocale('ru');

        $errors = Validator::make([], [
            'translations.uz.title' => ['required'],
            'names.kr' => ['required'],
            'slug' => ['required'],
        ])->errors();

        expect($errors->first('translations.uz.title'))->not->toContain('field is required')
            ->and($errors->first('translations.uz.title'))->not->toContain('translations.uz.title')
            ->and($errors->first('names.kr'))->not->toContain('names.kr')
            ->and($errors->first('slug'))->toContain('обязательно');
    })->group('feature', 'admin');

    it('translates the unique rule for duplicate slugs', function () {
        app()->setLocale('ru');

        $message = __('validation.unique', ['attribute' => 'slug']);

        expect($message)->not->toContain('has already been taken')
            ->and($message)->toContain('уже существует');
    })->group('feature', 'admin');
});
