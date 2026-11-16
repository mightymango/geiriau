<?php

namespace App\Providers;

use App\Services\Reminders\DailyReminders;
use App\Services\Reminders\LocalNotificationsGateway;
use App\Services\Reminders\LogGateway;
use App\Services\Reminders\NotificationGateway;
use App\Services\StudyPlanner;
use App\Srs\Scheduler;
use App\Srs\SchedulerConfig;
use App\Srs\StudyDay;
use Illuminate\Support\ServiceProvider;

class StudyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StudyDay::class, fn () => new StudyDay(
            (int) config('cymraeg.rollover_hour', 3),
            config('cymraeg.timezone', 'Europe/London'),
        ));

        $this->app->singleton(Scheduler::class, fn ($app) => new Scheduler(
            new SchedulerConfig(
                learningSteps: config('cymraeg.learning_steps', [1, 10]),
                relearningSteps: config('cymraeg.relearning_steps', [10]),
            ),
            $app->make(StudyDay::class),
        ));

        $this->app->singleton(StudyPlanner::class);

        $this->app->singleton(NotificationGateway::class, function () {
            $driver = config('cymraeg.reminder_driver')
                ?: (class_exists(\Smronju\NativephpLocalNotifications\Facades\LocalNotifications::class) ? 'local' : 'log');

            return $driver === 'local' ? new LocalNotificationsGateway : new LogGateway;
        });

        $this->app->singleton(DailyReminders::class);
    }
}
