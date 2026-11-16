<?php

use App\Models\Setting;
use App\NativeComponents\Home;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));
});

function onboard(): StudyPlanner
{
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    return $planner;
}

it('sends you to setup on first launch', function () {
    Native::test(Home::class)
        ->assertExitedToWeb('/settings');
});

it('greets you by name', function () {
    onboard();
    Setting::put('name', 'Cerys');

    Native::test(Home::class)
        ->assertSee('Shwmae, Cerys!');
});

it('greets you without a name when none is given', function () {
    onboard();

    Native::test(Home::class)
        ->assertSee('Shwmae!');
});

it('shows the app tabs with Heddiw active', function () {
    onboard();

    Native::visit('/')
        ->assertHasTabBar()
        ->assertHasTab('Heddiw')
        ->assertHasTab('Ymarfer')
        ->assertHasTab('Geiriau')
        ->assertHasTab('Gosodiadau')
        ->assertTabActive('Heddiw');
});

it('lists today\'s new words and opens them to learn', function () {
    $words = onboard()->todaysNewWords();

    Native::test(Home::class)
        ->assertSee($words->first()->cy)
        ->tap("Meet today's words")
        ->assertNavigatedTo('/learn');
});

it('starts practice when cards are due', function () {
    onboard();

    Native::test(Home::class)
        ->tap('Dechrau ymarfer · 5')
        ->assertExitedToWeb('/study');
});

it('offers more words once everything is done', function () {
    $planner = onboard();
    $planner->todaysNewWords();

    while ($card = $planner->nextCard()) {
        $planner->answer($card, Grade::Easy);
    }

    Native::test(Home::class)
        ->assertSee('Da iawn! Nothing due right now.')
        ->tap('Mwy o eiriau · 5 more words')
        ->assertNavigatedTo('/learn');

    expect($planner->todaysNewWords())->toHaveCount(10);
});
