<?php

use App\Models\Word;
use App\NativeComponents\Words;
use App\Services\StudyPlanner;
use Carbon\CarbonImmutable;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));

    app(StudyPlanner::class)->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
});

it('lists words with their meaning and progress', function () {
    $word = Word::query()->where('cy', 'cath')->first();

    Native::test(Words::class)
        ->assertSee('cath')
        ->assertSee("{$word->en} · eb")
        ->assertSee('to come');
});

it('searches in Welsh and English', function () {
    Native::test(Words::class)
        ->set('search', 'cath')
        ->assertSee('cath')
        ->assertDontSee('dog');
});

it('filters by level', function () {
    Native::test(Words::class)
        ->set('levelIndex', 3)
        ->assertSee('Dim geiriau — no words found.');
});

it('adds your own word and starts learning it today', function () {
    Native::test(Words::class)
        ->tap('Add word')
        ->assertSet('adding', true)
        ->set('cy', 'pwdin')
        ->set('en', 'pudding')
        ->set('genderIndex', 1)
        ->tap('Cadw')
        ->assertSet('adding', false);

    $word = Word::query()->where('cy', 'pwdin')->first();

    expect($word->level)->toBe('own')
        ->and($word->gender)->toBe('m')
        ->and($word->introduced_at)->not->toBeNull();
});

it('needs both the Welsh and the English', function () {
    $before = Word::query()->count();

    Native::test(Words::class)
        ->tap('Add word')
        ->set('cy', 'pwdin')
        ->tap('Cadw')
        ->assertSet('adding', true);

    expect(Word::query()->count())->toBe($before);
});

it('is the Geiriau tab', function () {
    Native::visit('/words')
        ->assertHasTabBar()
        ->assertTabActive('Geiriau');
});
