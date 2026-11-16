<?php

use App\Models\Setting;
use App\Models\Word;
use App\NativeComponents\Settings;
use App\Services\StudyPlanner;
use Carbon\CarbonImmutable;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));

    $this->planner = app(StudyPlanner::class);
});

it('welcomes you on first launch without the tab bar', function () {
    Native::visit('/settings')
        ->assertSee('Croeso!')
        ->assertSee("A few quick choices and you're ready.")
        ->assertTabBarHidden();
});

it('sets you up and takes you home', function () {
    Native::test(Settings::class)
        ->set('name', ' Cerys ')
        ->set('mynediadMode', StudyPlanner::MYNEDIAD_OFF)
        ->set('newPerDay', 7)
        ->set('reminderTime', '07:30')
        ->tap('Dechrau')
        ->assertReplacedWith('/');

    expect($this->planner->isOnboarded())->toBeTrue()
        ->and(Setting::read('name'))->toBe('Cerys')
        ->and($this->planner->newPerDay())->toBe(7)
        ->and($this->planner->mynediadMode())->toBe(StudyPlanner::MYNEDIAD_OFF)
        ->and(Setting::read('reminder_time'))->toBe('07:30');
});

it('defaults the reminder to eight in the morning', function () {
    Native::test(Settings::class)
        ->assertSet('reminderTime', '08:00');
});

it('is the Gosodiadau tab once you are set up', function () {
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    Native::visit('/settings')
        ->assertSee('Gosodiadau')
        ->assertTabBarVisible()
        ->assertTabActive('Gosodiadau');
});

it('saves changes', function () {
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    Native::test(Settings::class)
        ->set('newPerDay', 9)
        ->set('name', 'Dafydd')
        ->tap('Cadw')
        ->assertSee('Wedi cadw ✓');

    expect($this->planner->newPerDay())->toBe(9)
        ->and(Setting::read('name'))->toBe('Dafydd');
});

it('limits the name to a sensible length', function () {
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    Native::test(Settings::class)
        ->set('name', str_repeat('a', 41))
        ->tap('Cadw')
        ->assertDontSee('Wedi cadw ✓');

    expect(Setting::read('name'))->toBeNull();
});

it('asks before wiping progress', function () {
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    $this->planner->todaysNewWords();

    Native::test(Settings::class)
        ->tap('Reset progress')
        ->assertNativeCalled('Dialog.Alert')
        ->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Reset', 'id' => 'reset-progress'])
        ->assertReplacedWith('/settings');

    expect($this->planner->isOnboarded())->toBeFalse()
        ->and(Word::query()->whereNotNull('introduced_at')->count())->toBe(0);
});

it('keeps progress when you cancel the reset', function () {
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    Native::test(Settings::class)
        ->tap('Reset progress')
        ->emitNative(ButtonPressed::class, ['index' => 0, 'label' => 'Cancel', 'id' => 'reset-progress'])
        ->assertNoNavigation();

    expect($this->planner->isOnboarded())->toBeTrue();
});
