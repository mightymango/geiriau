<?php

use App\NativeComponents\Learn;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));

    $this->planner = app(StudyPlanner::class);
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    $this->words = $this->planner->todaysNewWords()->values();
});

it('shows today\'s first word with its meaning', function () {
    $word = $this->words->first();

    Native::test(Learn::class)
        ->assertSee('Gair newydd 1 / 5')
        ->assertSee($word->cy)
        ->assertSee($word->en);
});

it('steps forwards and back through the words', function () {
    Native::test(Learn::class)
        ->tap('Nesaf')
        ->assertSet('index', 1)
        ->assertSee($this->words[1]->cy)
        ->tap('Yn ôl')
        ->assertSet('index', 0);
});

it('offers practice after the last word', function () {
    Native::test(Learn::class)
        ->set('index', 4)
        ->assertDontSee('Nesaf')
        ->tap('Practise now')
        ->assertReplacedWith('/study');
});

it('starts at the first word not yet seen', function () {
    while ($card = $this->planner->nextCard()) {
        $this->planner->answer($card, Grade::Easy);
    }

    $this->planner->learnMore();

    Native::test(Learn::class)
        ->assertSet('index', 5)
        ->assertSee('Gair newydd 6 / 10');
});

it('says so when there are no new words today', function () {
    $this->words->each->update(['intro_day' => null]);

    Native::test(Learn::class)
        ->assertSee('No new words today.');
});

it('ticks as you step through the words', function () {
    Native::test(Learn::class)
        ->tap('Nesaf')
        ->assertNativeCalled('GeiriauHaptics.Selection');
});
