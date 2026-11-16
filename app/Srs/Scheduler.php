<?php

namespace App\Srs;

use Closure;
use DateTimeImmutable;

/**
 * SM-2 family scheduler (the approach used by Anki's classic scheduler).
 *
 * New card  → short in-session steps (1 min, 10 min) → graduates to 1 day
 * Review    → interval grows by the card's ease (≈ ×2.5) each time it's remembered
 * Forgotten → ease drops, interval shrinks, card goes through a short relearning step
 */
final class Scheduler
{
    private Closure $random;

    public function __construct(
        private SchedulerConfig $config = new SchedulerConfig,
        private StudyDay $day = new StudyDay,
        ?Closure $random = null,
    ) {
        $this->random = $random ?? fn (int $min, int $max) => random_int($min, $max);
    }

    public function answer(CardState $current, Grade $grade, DateTimeImmutable $now): CardState
    {
        $next = clone $current;
        $next->reps++;

        return match ($current->state) {
            CardState::NEW, CardState::LEARNING => $this->stepped($next, $grade, $now, $this->config->learningSteps, true),
            CardState::RELEARNING => $this->stepped($next, $grade, $now, $this->config->relearningSteps, false),
            default => $this->review($next, $grade, $now),
        };
    }

    /**
     * What each button would do, for showing "10m / 1d / 4d" under the grade buttons.
     *
     * @return array<int, string> keyed by Grade value
     */
    public function preview(CardState $current, DateTimeImmutable $now): array
    {
        $out = [];
        foreach (Grade::cases() as $grade) {
            $copy = clone $current;
            $out[$grade->value] = $this->humanise($this->answerWithoutFuzz($copy, $grade, $now), $now);
        }

        return $out;
    }

    private function answerWithoutFuzz(CardState $state, Grade $grade, DateTimeImmutable $now): CardState
    {
        $plain = new self(
            new SchedulerConfig(...[...get_object_vars($this->config), 'fuzz' => false]),
            $this->day,
            $this->random,
        );

        return $plain->answer($state, $grade, $now);
    }

    private function stepped(CardState $s, Grade $grade, DateTimeImmutable $now, array $steps, bool $fresh): CardState
    {
        $s->state = $fresh ? CardState::LEARNING : CardState::RELEARNING;
        $s->step = min($s->step, count($steps) - 1);

        switch ($grade) {
            case Grade::Again:
                $s->step = 0;

                return $this->inMinutes($s, $steps[0], $now);

            case Grade::Hard:
                $current = $steps[$s->step];
                $delay = isset($steps[$s->step + 1])
                    ? ($current + $steps[$s->step + 1]) / 2
                    : $current * 1.5;

                return $this->inMinutes($s, (int) round($delay), $now);

            case Grade::Good:
                $s->step++;
                if ($s->step >= count($steps)) {
                    return $this->graduate($s, $fresh ? $this->config->graduatingInterval : max(1, $s->interval), $now);
                }

                return $this->inMinutes($s, $steps[$s->step], $now);

            case Grade::Easy:
            default:
                return $this->graduate($s, $fresh ? $this->config->easyInterval : max(1, $s->interval) + 1, $now);
        }
    }

    private function review(CardState $s, Grade $grade, DateTimeImmutable $now): CardState
    {
        $c = $this->config;
        $late = max(0, $this->day->daysBetween($s->due ?? $now, $now));
        $ivl = max(1, $s->interval);

        if ($grade === Grade::Again) {
            $s->lapses++;
            $s->ease = max($c->minimumEase, $s->ease - 0.2);
            $s->interval = max(1, (int) round($ivl * $c->lapseMultiplier));
            $s->state = CardState::RELEARNING;
            $s->step = 0;

            return $this->inMinutes($s, $c->relearningSteps[0], $now);
        }

        $hard = max($ivl + 1, (int) round($ivl * $c->hardFactor));
        $good = max($hard + 1, (int) round(($ivl + $late / 2) * $s->ease));
        $easy = max($good + 1, (int) round(($ivl + $late) * $s->ease * $c->easyBonus));

        [$days, $easeChange] = match ($grade) {
            Grade::Hard => [$hard, -0.15],
            Grade::Good => [$good, 0.0],
            default => [$easy, 0.15],
        };

        $s->ease = max($c->minimumEase, $s->ease + $easeChange);

        return $this->schedule($s, $this->fuzz($days), $now);
    }

    private function graduate(CardState $s, int $days, DateTimeImmutable $now): CardState
    {
        $s->state = CardState::REVIEW;
        $s->step = 0;

        return $this->schedule($s, $days, $now);
    }

    private function schedule(CardState $s, int $days, DateTimeImmutable $now): CardState
    {
        $days = min($this->config->maximumInterval, max(1, $days));
        $s->interval = $days;
        $s->due = $this->day->startPlus($now, $days);

        return $s;
    }

    private function inMinutes(CardState $s, int $minutes, DateTimeImmutable $now): CardState
    {
        $s->due = $now->modify("+{$minutes} minutes");

        return $s;
    }

    /** Spread cards out a little so a day's batch doesn't all come back on the same day. */
    private function fuzz(int $days): int
    {
        if (! $this->config->fuzz || $days < 3) {
            return $days;
        }
        $range = max(1, (int) round($days * 0.05));

        return $days + ($this->random)(-$range, $range);
    }

    private function humanise(CardState $s, DateTimeImmutable $now): string
    {
        if ($s->state === CardState::REVIEW) {
            $d = $s->interval;

            return match (true) {
                $d < 30 => "{$d}d",
                $d < 365 => round($d / 30, 1).'mo',
                default => round($d / 365, 1).'y',
            };
        }

        $mins = max(1, (int) round(($s->due->getTimestamp() - $now->getTimestamp()) / 60));

        return "{$mins}m";
    }
}
