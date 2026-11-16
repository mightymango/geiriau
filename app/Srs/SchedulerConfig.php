<?php

namespace App\Srs;

final class SchedulerConfig
{
    /**
     * @param  int[]  $learningSteps  minutes between in-session repeats for a new card
     * @param  int[]  $relearningSteps  minutes between repeats after a forgotten review
     */
    public function __construct(
        public readonly array $learningSteps = [1, 10],
        public readonly array $relearningSteps = [10],
        public readonly int $graduatingInterval = 1,
        public readonly int $easyInterval = 4,
        public readonly float $startingEase = 2.5,
        public readonly float $minimumEase = 1.3,
        public readonly float $hardFactor = 1.2,
        public readonly float $easyBonus = 1.3,
        public readonly float $lapseMultiplier = 0.3,
        public readonly int $maximumInterval = 365,
        public readonly bool $fuzz = true,
    ) {}
}
