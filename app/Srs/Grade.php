<?php

namespace App\Srs;

enum Grade: int
{
    case Again = 1;
    case Hard = 2;
    case Good = 3;
    case Easy = 4;

    public function label(): string
    {
        return match ($this) {
            self::Again => 'Eto',      // again
            self::Hard => 'Anodd',     // hard
            self::Good => 'Da',        // good
            self::Easy => 'Hawdd',     // easy
        };
    }

    public function englishLabel(): string
    {
        return $this->name;
    }
}
