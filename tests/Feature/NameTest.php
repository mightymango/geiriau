<?php

use App\Livewire\Home;
use App\Livewire\Settings;
use App\Models\Setting;
use App\Services\StudyPlanner;
use Livewire\Livewire;

it('greets you by the name you give during setup', function () {
    Livewire::test(Settings::class)
        ->set('name', '  Cerys ')
        ->call('save');

    expect(Setting::read('name'))->toBe('Cerys');

    Livewire::test(Home::class)->assertSee('Shwmae, Cerys!');
});

it('greets you without a name when none is given', function () {
    app(StudyPlanner::class)->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    Livewire::test(Home::class)
        ->assertSee('Shwmae!')
        ->assertDontSee('Simon');
});

it('lets you change your name later', function () {
    app(StudyPlanner::class)->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    Setting::put('name', 'Cerys');

    Livewire::test(Settings::class)
        ->assertSet('name', 'Cerys')
        ->set('name', 'Dafydd')
        ->call('save');

    expect(Setting::read('name'))->toBe('Dafydd');
});

it('limits the name to a sensible length', function () {
    Livewire::test(Settings::class)
        ->set('name', str_repeat('a', 41))
        ->call('save')
        ->assertHasErrors('name');
});
