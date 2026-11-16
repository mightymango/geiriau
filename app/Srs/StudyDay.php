<?php

namespace App\Srs;

use DateTimeImmutable;
use DateTimeZone;

/**
 * A "study day" runs from the rollover hour (default 03:00) to the same time the next day,
 * so a late-night session still counts as the same day.
 */
final class StudyDay
{
    private DateTimeZone $tz;

    public function __construct(private int $rolloverHour = 3, string $timezone = 'Europe/London')
    {
        $this->tz = new DateTimeZone($timezone);
    }

    public function start(DateTimeImmutable $now): DateTimeImmutable
    {
        $local = $now->setTimezone($this->tz);
        $start = $local->setTime($this->rolloverHour, 0);

        return $local < $start ? $start->modify('-1 day') : $start;
    }

    public function key(DateTimeImmutable $now): string
    {
        return $this->start($now)->format('Y-m-d');
    }

    /** Start of the study day $days after today. */
    public function startPlus(DateTimeImmutable $now, int $days): DateTimeImmutable
    {
        return $this->start($now)->modify("+{$days} days");
    }

    /** Whole study days from $from to $to (negative if $to is earlier). */
    public function daysBetween(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        $a = $this->start($from);
        $b = $this->start($to);

        return (int) round(($b->getTimestamp() - $a->getTimestamp()) / 86400);
    }
}
