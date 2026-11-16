<?php

use App\Services\Reminders\DailyReminders;
use App\Services\Reminders\NotificationGateway;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;

class FakeGateway implements NotificationGateway
{
    public array $scheduled = [];

    public array $cancelled = [];

    public int $permissionRequests = 0;

    public function requestPermission(): void
    {
        $this->permissionRequests++;
    }

    public function schedule(string $id, string $title, string $body, DateTimeInterface $at): void
    {
        $this->scheduled[$id] = compact('title', 'body', 'at');
    }

    public function cancel(string $id): void
    {
        unset($this->scheduled[$id]);

        $this->cancelled[] = $id;
    }
}

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));

    $this->gateway = new FakeGateway;
    $this->app->instance(NotificationGateway::class, $this->gateway);

    app(StudyPlanner::class)->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
});

it('schedules a week of reminders at the chosen time', function () {
    app(DailyReminders::class)->configure(true, '18:30');

    $today = $this->gateway->scheduled['geiriau-2026-11-16'];

    expect($this->gateway->scheduled)->toHaveCount(7)
        ->and($today['at']->format('Y-m-d H:i'))->toBe('2026-11-16 18:30')
        ->and($today['body'])->toContain('5 new words');
});

it('skips today once everything is done', function () {
    $planner = app(StudyPlanner::class);
    $planner->todaysNewWords();

    while ($card = $planner->nextCard()) {
        $planner->answer($card, Grade::Easy);
    }

    app(DailyReminders::class)->sync();

    expect($this->gateway->scheduled)
        ->not->toHaveKey('geiriau-2026-11-16')
        ->toHaveKey('geiriau-2026-11-17');
});

it('cancels reminders when they are turned off', function () {
    $reminders = app(DailyReminders::class);
    $reminders->configure(true, '19:00');
    $reminders->configure(false, '19:00');

    expect($this->gateway->cancelled)->toHaveCount(7)
        ->and($this->gateway->scheduled)->toBe([]);
});

it('starts tomorrow when the reminder time has already passed today', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 20:00:00'));

    app(DailyReminders::class)->configure(true, '19:00');

    expect($this->gateway->scheduled)
        ->not->toHaveKey('geiriau-2026-11-16')
        ->toHaveCount(6);
});

it('defaults the reminder time to eight in the morning', function () {
    expect(app(DailyReminders::class)->time())->toBe('08:00');
});
