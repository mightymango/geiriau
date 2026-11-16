<?php

namespace App\Services\Reminders;

use App\Models\Setting;
use App\Services\StudyPlanner;
use App\Srs\StudyDay;
use Carbon\CarbonImmutable;

/**
 * One reminder a day at a time you choose, so practice hooks onto a fixed point in your routine.
 *
 * Rather than a blind repeating alarm, the next 7 days are scheduled as one-off notifications
 * and rebuilt every time the app is opened or a session finishes. That means:
 *  - no reminder today once you've cleared today's cards,
 *  - today's reminder says how much is actually waiting,
 *  - if you stop opening the app, the reminders stop after a week instead of nagging for ever.
 */
class DailyReminders
{
    public const DAYS_AHEAD = 7;

    private const PROMPTS = [
        ['Amser am eiriau newydd!', 'Time for your new words — five minutes is plenty.'],
        ['Pum gair y dydd', 'Five words a day keeps the hiraeth away.'],
        ['Dal ati!', 'Keep going! Your words are waiting.'],
        ['Paned a geiriau?', 'A cuppa and a quick practice?'],
        ['Ymlaen â ni', 'Onwards — today\'s Welsh is ready.'],
        ['Gwych hyd yn hyn', 'Great so far — don\'t break the streak.'],
        ['Tipyn bach bob dydd', 'A little every day — that\'s how it sticks.'],
    ];

    public function __construct(
        private NotificationGateway $gateway,
        private StudyPlanner $planner,
        private StudyDay $day,
    ) {}

    public function enabled(): bool
    {
        return Setting::read('reminder_enabled', '1') === '1';
    }

    /** "HH:MM", local time. */
    public function time(): string
    {
        return Setting::read('reminder_time', config('cymraeg.reminder_time', '19:00'));
    }

    public function configure(bool $enabled, string $time): void
    {
        $wasEnabled = $this->enabled();
        Setting::put('reminder_enabled', $enabled ? '1' : '0');
        Setting::put('reminder_time', $time);

        if ($enabled && ! $wasEnabled) {
            $this->gateway->requestPermission();
        }

        $this->sync();
    }

    public function requestPermission(): void
    {
        $this->gateway->requestPermission();
    }

    /** Rebuild the upcoming reminders. Safe to call often. */
    public function sync(): void
    {
        $previous = json_decode(Setting::read('reminder_ids', '[]'), true) ?: [];
        $scheduled = [];

        if ($this->enabled() && $this->planner->isOnboarded()) {
            foreach ($this->upcoming() as $id => [$at, $title, $body]) {
                $this->gateway->schedule($id, $title, $body, $at);
                $scheduled[] = $id;
            }
        }

        foreach (array_diff($previous, $scheduled) as $stale) {
            $this->gateway->cancel($stale);
        }

        Setting::put('reminder_ids', json_encode(array_values($scheduled)));
    }

    /** @return array<string, array{0: CarbonImmutable, 1: string, 2: string}> */
    public function upcoming(): array
    {
        $now = $this->planner->now();
        $tz = config('cymraeg.timezone', 'Europe/London');
        [$hour, $minute] = array_map('intval', explode(':', $this->time()) + [1 => 0]);
        $today = CarbonImmutable::parse($this->day->key($now), $tz);

        $out = [];
        for ($d = 0; $d < self::DAYS_AHEAD; $d++) {
            $date = $today->addDays($d);
            $at = $date->setTime($hour, $minute);

            if ($d === 0 && ($at->lessThanOrEqualTo($now) || $this->doneForToday())) {
                continue;
            }

            [$title, $body] = $d === 0 ? $this->todayMessage() : self::PROMPTS[$date->dayOfYear % count(self::PROMPTS)];
            $out['geiriau-'.$date->format('Y-m-d')] = [$at, $title, $body];
        }

        return $out;
    }

    private function doneForToday(): bool
    {
        $stats = $this->planner->stats();

        return $stats['due'] === 0 && $stats['reviewed_today'] > 0;
    }

    /** @return array{0: string, 1: string} */
    private function todayMessage(): array
    {
        $due = $this->planner->stats()['due'];
        $new = $this->planner->todaysNewWords()->filter(fn ($w) => $w->recognise?->state === 'new')->count();

        $parts = array_filter([
            $due > 0 ? "{$due} ".($due === 1 ? 'card' : 'cards').' to practise' : null,
            $new > 0 ? "{$new} new ".($new === 1 ? 'word' : 'words') : null,
        ]);

        return ['Amser ymarfer!', $parts ? ucfirst(implode(' · ', $parts)).'.' : 'Time for today\'s Welsh.'];
    }
}
