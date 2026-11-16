<?php

use App\Models\Card;
use App\Models\Setting;
use App\Models\Word;
use App\NativeComponents\Study;
use App\Services\StudyPlanner;
use Carbon\CarbonImmutable;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));

    $this->planner = app(StudyPlanner::class);
    $this->planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
});

function onlyDueCard(Card $card): Card
{
    Setting::put('intro_day', app(StudyPlanner::class)->todayKey());
    Card::query()->update(['active' => false]);

    $card->update(['active' => true, 'state' => 'review', 'interval' => 3, 'due_at' => now()->subHour()]);

    return $card;
}

function produceCardFor(string $cy, string $en): Card
{
    $word = Word::query()->firstOrCreate(['cy' => $cy, 'en' => $en], ['level' => 'own', 'position' => 9999]);

    return onlyDueCard($word->produce);
}

it('shows a Welsh word and reveals its meaning', function () {
    $word = $this->planner->todaysNewWords()->first();

    Native::test(Study::class)
        ->assertSee('Cymraeg → English')
        ->assertSee($word->cy)
        ->assertDontSee($word->en)
        ->tap('Dangos yr ateb')
        ->assertSee($word->en)
        ->assertSee('How well did you remember it?')
        ->assertSee('Eto')
        ->assertSee('Hawdd');
});

it('records the grade and moves to the next card', function () {
    $this->planner->todaysNewWords();
    $first = $this->planner->nextCard();

    Native::test(Study::class)
        ->tap('Dangos yr ateb')
        ->tap('Da')
        ->assertSet('doneThisSession', 1)
        ->assertSet('revealed', false);

    expect($first->fresh()->reps)->toBe(1);
});

it('checks a typed Welsh answer', function (string $typed, string $feedback) {
    produceCardFor('dŵr', 'water');

    Native::test(Study::class)
        ->assertSee('English → Cymraeg')
        ->assertSee('water')
        ->set('attempt', $typed)
        ->tap('Dangos yr ateb')
        ->assertSee($feedback);
})->with([
    'exact' => ['dŵr', 'Cywir — spot on'],
    'close' => ['dwr', 'Close — check the accents'],
    'wrong' => ['tân', 'You wrote “tân”'],
]);

it('celebrates when everything is done and offers more words', function () {
    Setting::put('intro_day', $this->planner->todayKey());
    Card::query()->update(['active' => false]);

    Native::test(Study::class)
        ->assertSee('Gwych!')
        ->assertSee('Nothing due right now.')
        ->tap('Mwy o eiriau · 5 more words')
        ->assertNavigatedTo('/learn');
});

it('goes home from the finished screen', function () {
    Setting::put('intro_day', $this->planner->todayKey());
    Card::query()->update(['active' => false]);

    Native::test(Study::class)
        ->tap('Adref')
        ->assertReplacedWith('/');
});

it('is the Ymarfer tab', function () {
    Native::visit('/study')
        ->assertHasTabBar()
        ->assertTabActive('Ymarfer');
});

it('taps lightly when you grade a card', function () {
    Native::test(Study::class)
        ->tap('Dangos yr ateb')
        ->tap('Da')
        ->assertNativeCalled('GeiriauHaptics.Impact');
});

it('gives feedback for a typed answer', function (string $typed, string $feedback) {
    produceCardFor('dŵr', 'water');

    Native::test(Study::class)
        ->set('attempt', $typed)
        ->tap('Dangos yr ateb')
        ->assertNativeCalled('GeiriauHaptics.Notify', fn (array $parameters) => $parameters['type'] === $feedback);
})->with([
    'exact' => ['dŵr', 'success'],
    'close' => ['dwr', 'warning'],
    'wrong' => ['tân', 'error'],
]);

it('celebrates finishing the session', function () {
    onlyDueCard(Card::query()->first());

    Native::test(Study::class)
        ->tap('Dangos yr ateb')
        ->tap('Da')
        ->assertNativeCalled('GeiriauHaptics.Notify', fn (array $parameters) => $parameters['type'] === 'success');
});
