<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Services\Reminders\DailyReminders;
use App\Services\Reminders\NotificationGateway;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRemindersTest extends TestCase
{
    use RefreshDatabase;

    private FakeGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));
        $this->gateway = new FakeGateway;
        $this->app->instance(NotificationGateway::class, $this->gateway);
        app(StudyPlanner::class)->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
    }

    public function test_schedules_a_week_of_reminders_at_the_chosen_time(): void
    {
        app(DailyReminders::class)->configure(true, '18:30');

        $this->assertCount(7, $this->gateway->scheduled);
        $today = $this->gateway->scheduled['geiriau-2026-11-16'];
        $this->assertSame('2026-11-16 18:30', $today['at']->format('Y-m-d H:i'));
        $this->assertStringContainsString('5 new words', $today['body']);
    }

    public function test_no_reminder_today_once_everything_is_done(): void
    {
        $planner = app(StudyPlanner::class);
        $planner->todaysNewWords();
        while ($card = $planner->nextCard()) {
            $planner->answer($card, Grade::Easy);
        }

        app(DailyReminders::class)->sync();

        $this->assertArrayNotHasKey('geiriau-2026-11-16', $this->gateway->scheduled);
        $this->assertArrayHasKey('geiriau-2026-11-17', $this->gateway->scheduled);
    }

    public function test_turning_reminders_off_cancels_them(): void
    {
        $reminders = app(DailyReminders::class);
        $reminders->configure(true, '19:00');
        $reminders->configure(false, '19:00');

        $this->assertCount(7, $this->gateway->cancelled);
        $this->assertSame([], $this->gateway->scheduled);
    }

    public function test_reminder_time_already_passed_today_starts_tomorrow(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-16 20:00:00'));
        app(DailyReminders::class)->configure(true, '19:00');

        $this->assertArrayNotHasKey('geiriau-2026-11-16', $this->gateway->scheduled);
        $this->assertCount(6, $this->gateway->scheduled);
    }
}

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
