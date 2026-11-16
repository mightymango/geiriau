<?php

use App\Services\WordBank;
use App\Srs\CardState;
use App\Srs\Grade;
use App\Srs\Scheduler;
use App\Srs\SchedulerConfig;
use App\Srs\StudyDay;

function scheduler(): Scheduler
{
    return new Scheduler(new SchedulerConfig(fuzz: false), new StudyDay(3, 'Europe/London'));
}

function londonTime(string $time): DateTimeImmutable
{
    return new DateTimeImmutable($time, new DateTimeZone('Europe/London'));
}

it('walks a new card through the learning steps then graduates it to tomorrow', function () {
    $scheduler = scheduler();

    $card = $scheduler->answer(new CardState, Grade::Good, londonTime('2026-11-16 10:00'));

    expect($card->state)->toBe('learning')
        ->and($card->due->format('H:i'))->toBe('10:10');

    $card = $scheduler->answer($card, Grade::Good, $card->due);

    expect($card->state)->toBe('review')
        ->and($card->interval)->toBe(1)
        ->and($card->due->setTimezone(new DateTimeZone('Europe/London'))->format('Y-m-d H:i'))->toBe('2026-11-17 03:00');
});

it('resets to the first step when answered again', function () {
    $scheduler = scheduler();
    $now = londonTime('2026-11-16 10:00');

    $card = $scheduler->answer(new CardState, Grade::Good, $now);
    $card = $scheduler->answer($card, Grade::Again, $now);

    expect($card->step)->toBe(0)
        ->and($card->due->format('H:i'))->toBe('10:01');
});

it('grows intervals and shrinks them on a lapse', function () {
    $scheduler = scheduler();
    $card = new CardState(CardState::REVIEW, 0, 2.5, 8, londonTime('2026-11-20 03:00'));

    $good = $scheduler->answer($card, Grade::Good, londonTime('2026-11-20 09:00'));

    expect($good->interval)->toBe(20);

    $lapse = $scheduler->answer($card, Grade::Again, londonTime('2026-11-20 09:00'));

    expect($lapse->state)->toBe('relearning')
        ->and($lapse->lapses)->toBe(1)
        ->and($lapse->ease)->toEqualWithDelta(2.3, 0.001)
        ->and($lapse->interval)->toBe(2);

    $back = $scheduler->answer($lapse, Grade::Good, $lapse->due);

    expect($back->state)->toBe('review')
        ->and($back->interval)->toBe(2);
});

it('counts a late night session as the previous day', function () {
    $day = new StudyDay(3, 'Europe/London');

    expect($day->key(londonTime('2026-11-17 01:30')))->toBe('2026-11-16')
        ->and($day->key(londonTime('2026-11-17 03:00')))->toBe('2026-11-17');
});

it('parses levels and genders from the word bank', function () {
    $words = WordBank::parse(file_get_contents(__DIR__.'/../../database/data/words.txt'));
    $levels = array_count_values(array_column($words, 'level'));
    $pairs = array_map(fn ($word) => "{$word['cy']}|{$word['en']}", $words);
    $cath = array_values(array_filter($words, fn ($word) => $word['cy'] === 'cath'))[0];

    expect($levels['mynediad'])->toBeGreaterThan(150)
        ->and($levels['sylfaen'])->toBeGreaterThan(200)
        ->and($pairs)->toHaveCount(count(array_unique($pairs)))
        ->and($cath['gender'])->toBe('f');
});
