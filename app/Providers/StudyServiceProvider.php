<?php

namespace App\Providers;

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
    }
}
