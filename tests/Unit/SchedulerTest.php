<?php

namespace Tests\Unit;

use App\Services\WordBank;
use App\Srs\CardState;
use App\Srs\Grade;
use App\Srs\Scheduler;
use App\Srs\SchedulerConfig;
use App\Srs\StudyDay;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class SchedulerTest extends TestCase
{
    private function scheduler(): Scheduler
    {
        return new Scheduler(new SchedulerConfig(fuzz: false), new StudyDay(3, 'Europe/London'));
    }

    private function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable($time, new DateTimeZone('Europe/London'));
    }

    public function test_new_card_walks_through_learning_steps_then_graduates_to_tomorrow(): void
    {
        $s = $this->scheduler();
        $now = $this->at('2026-11-16 10:00');

        $card = $s->answer(new CardState, Grade::Good, $now);
        $this->assertSame('learning', $card->state);
        $this->assertSame('10:10', $card->due->format('H:i'));

        $card = $s->answer($card, Grade::Good, $card->due);
        $this->assertSame('review', $card->state);
        $this->assertSame(1, $card->interval);
        $this->assertSame('2026-11-17 03:00', $card->due->setTimezone(new DateTimeZone('Europe/London'))->format('Y-m-d H:i'));
    }

    public function test_again_resets_to_first_step(): void
    {
        $s = $this->scheduler();
        $now = $this->at('2026-11-16 10:00');
        $card = $s->answer(new CardState, Grade::Good, $now);
        $card = $s->answer($card, Grade::Again, $now);

        $this->assertSame(0, $card->step);
        $this->assertSame('10:01', $card->due->format('H:i'));
    }

    public function test_intervals_grow_and_lapses_shrink_them(): void
    {
        $s = $this->scheduler();
        $card = new CardState(CardState::REVIEW, 0, 2.5, 8, $this->at('2026-11-20 03:00'));

        $good = $s->answer($card, Grade::Good, $this->at('2026-11-20 09:00'));
        $this->assertSame(20, $good->interval);

        $lapse = $s->answer($card, Grade::Again, $this->at('2026-11-20 09:00'));
        $this->assertSame('relearning', $lapse->state);
        $this->assertSame(1, $lapse->lapses);
        $this->assertEqualsWithDelta(2.3, $lapse->ease, 0.001);
        $this->assertSame(2, $lapse->interval);

        $back = $s->answer($lapse, Grade::Good, $lapse->due);
        $this->assertSame('review', $back->state);
        $this->assertSame(2, $back->interval);
    }

    public function test_late_night_session_counts_as_previous_day(): void
    {
        $day = new StudyDay(3, 'Europe/London');
        $this->assertSame('2026-11-16', $day->key($this->at('2026-11-17 01:30')));
        $this->assertSame('2026-11-17', $day->key($this->at('2026-11-17 03:00')));
    }

    public function test_word_bank_parses_levels_and_genders(): void
    {
        $words = WordBank::parse(file_get_contents(__DIR__.'/../../database/data/words.txt'));
        $levels = array_count_values(array_column($words, 'level'));

        $this->assertGreaterThan(150, $levels['mynediad']);
        $this->assertGreaterThan(200, $levels['sylfaen']);

        $keys = array_map(fn ($w) => $w['cy'].'|'.$w['en'], $words);
        $this->assertSame(count($keys), count(array_unique($keys)), 'duplicate Welsh/English pair');

        $cath = array_values(array_filter($words, fn ($w) => $w['cy'] === 'cath'))[0];
        $this->assertSame('f', $cath['gender']);
    }
}
