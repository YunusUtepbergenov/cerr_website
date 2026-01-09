<?php

use App\Livewire\Contact;
use App\Livewire\Leadership;
use App\Livewire\Structure;
use Livewire\Livewire;

describe('Contact Component', function () {
    it('renders successfully', function () {
        Livewire::test(Contact::class)
            ->assertStatus(200);
    })->group('feature', 'livewire');
});

describe('Leadership Component', function () {
    it('renders successfully', function () {
        Livewire::test(Leadership::class)
            ->assertStatus(200);
    })->group('feature', 'livewire');
});

describe('Structure Component', function () {
    it('renders successfully', function () {
        Livewire::test(Structure::class)
            ->assertStatus(200);
    })->group('feature', 'livewire');
});
