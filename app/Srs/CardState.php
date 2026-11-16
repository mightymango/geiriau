<?php

namespace App\Srs;

use DateTimeImmutable;

/**
 * Plain value object describing where a card sits in the spaced-repetition cycle.
 * Kept free of Laravel so the scheduling maths can be tested in isolation.
 */
final class CardState
{
    public const NEW = 'new';
    public const LEARNING = 'learning';
    public const REVIEW = 'review';
    public const RELEARNING = 'relearning';

    public function __construct(
        public string $state = self::NEW,
        public int $step = 0,
        public float $ease = 2.5,
        public int $interval = 0,          // days (review cards only)
        public ?DateTimeImmutable $due = null,
        public int $reps = 0,
        public int $lapses = 0,
    ) {}

    public function isLearning(): bool
    {
        return in_array($this->state, [self::NEW, self::LEARNING, self::RELEARNING], true);
    }
}
