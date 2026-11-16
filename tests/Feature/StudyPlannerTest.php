<?php

use App\Livewire\Home;
use App\Livewire\Learn;
use App\Models\Card;
use App\Models\Word;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));
});

it('loads the word bank in migrations', function () {
    expect(Word::count())->toBeGreaterThan(400)
        ->and(Card::count())->toBe(Word::count() * 2);
});

it('shows settings on first launch', function () {
    $this->get('/settings')
        ->assertOk()
        ->assertSee('Croeso');
});

it('introduces five new sylfaen words a day', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

    $today = $planner->todaysNewWords();

    expect($today)->toHaveCount(5)
        ->and($today->pluck('level')->unique()->values()->all())->toBe(['sylfaen'])
        ->and($planner->todaysNewWords())->toHaveCount(5);

    $this->travelTo(CarbonImmutable::parse('2026-11-17 10:00:00'));

    expect($planner->todaysNewWords())->toHaveCount(5)
        ->and(Word::whereNotNull('introduced_at')->count())->toBe(10);
});

it('unlocks production the next day once a word is recognised', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    $planner->todaysNewWords();

    $card = $planner->nextCard();

    expect($card->direction)->toBe(Card::RECOGNISE)
        ->and($card->state)->toBe('new');

    $planner->answer($card, Grade::Good);
    $planner->answer($card->fresh(), Grade::Good);

    $produce = Card::where('word_id', $card->word_id)->where('direction', Card::PRODUCE)->first();

    expect($card->fresh()->state)->toBe('review')
        ->and($produce->active)->toBeTrue()
        ->and($produce->state)->toBe('learning')
        ->and($produce->due_at->greaterThan(now()))->toBeTrue();
});

it('spreads out reviews when mynediad is marked as known', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_REVIEW, 5);

    $mynediadCards = Card::whereHas('word', fn ($query) => $query->where('level', 'mynediad'));
    $activeCount = (clone $mynediadCards)->where('active', true)->count();
    $dueNowCount = (clone $mynediadCards)->where('due_at', '<=', now())->count();

    expect($activeCount)->toBe(Word::where('level', 'mynediad')->count() * 2)
        ->and($dueNowCount)->toBe(config('cymraeg.mynediad_per_day'));
});

function finishTodaysWords(StudyPlanner $planner): void
{
    $planner->todaysNewWords();

    while ($card = $planner->nextCard()) {
        $planner->answer($card, Grade::Easy);
    }
}

it('offers more words once today\'s words are done', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    $planner->todaysNewWords();

    expect($planner->canLearnMore())->toBeFalse();

    finishTodaysWords($planner);

    expect($planner->canLearnMore())->toBeTrue();
});

it('adds another batch to today\'s words on request', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    finishTodaysWords($planner);

    $planner->learnMore();

    expect($planner->todaysNewWords())->toHaveCount(10)
        ->and($planner->nextCard()->state)->toBe('new')
        ->and($planner->canLearnMore())->toBeFalse();
});

it('does not offer more words while reviews are piling up', function () {
    config(['cymraeg.backlog_limit' => 0]);

    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_REVIEW, 5);
    finishTodaysWords($planner);

    $this->travelTo(CarbonImmutable::parse('2026-11-20 10:00:00'));

    expect($planner->dueReviewCount())->toBeGreaterThan(0)
        ->and($planner->canLearnMore())->toBeFalse();
});

it('starts learning at the first word not yet seen', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    finishTodaysWords($planner);
    $planner->learnMore();

    Livewire::test(Learn::class)->assertSet('index', 5);
});

it('sends you to meet the extra words from home', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    finishTodaysWords($planner);

    Livewire::test(Home::class)
        ->assertSee('5 more words')
        ->call('learnMore')
        ->assertRedirect(route('learn'));

    expect($planner->todaysNewWords())->toHaveCount(10);
});

it('does not offer more words before today\'s batch is picked', function () {
    $planner = app(StudyPlanner::class);
    $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    finishTodaysWords($planner);

    $this->travelTo(CarbonImmutable::parse('2026-11-17 10:00:00'));

    expect($planner->canLearnMore())->toBeFalse();
});
