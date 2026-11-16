<?php

namespace App\NativeComponents;

use App\Models\Setting;
use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use Illuminate\View\View;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Concerns\ValidatesInput;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

class Settings extends NativeComponent
{
    use ValidatesInput;

    private const RESET_ALERT = 'reset-progress';

    public string $name = '';

    public int $newPerDay = 5;

    public string $mynediadMode = StudyPlanner::MYNEDIAD_REVIEW;

    public bool $onboarded = false;

    public bool $reminderEnabled = true;

    public string $reminderTime = '08:00';

    public bool $saved = false;

    public function mount(StudyPlanner $planner, DailyReminders $reminders): void
    {
        $this->name = (string) Setting::read('name', '');
        $this->reminderEnabled = $reminders->enabled();
        $this->reminderTime = $reminders->time();
        $this->onboarded = $planner->isOnboarded();
        $this->newPerDay = $planner->newPerDay();
        $this->mynediadMode = $planner->mynediadMode();
        $this->hidesTabBar = ! $this->onboarded;
    }

    public function save(StudyPlanner $planner, DailyReminders $reminders): void
    {
        $this->validate([
            'name' => ['nullable', 'string', 'max:40'],
            'newPerDay' => ['required', 'integer', 'min:1', 'max:20'],
            'mynediadMode' => ['required', 'in:review,learn,off'],
            'reminderTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
        ]);

        Setting::put('name', trim($this->name));

        if (! $this->onboarded) {
            $planner->onboard($this->mynediadMode, $this->newPerDay);
            Setting::put('reminder_enabled', $this->reminderEnabled ? '1' : '0');
            Setting::put('reminder_time', $this->reminderTime);

            if ($this->reminderEnabled) {
                $reminders->requestPermission();
            }

            $reminders->sync();

            $this->replace('/');

            return;
        }

        Setting::put('new_per_day', $this->newPerDay);

        if ($this->mynediadMode !== $planner->mynediadMode()) {
            $planner->setMynediadMode($this->mynediadMode);
        }

        $reminders->configure($this->reminderEnabled, $this->reminderTime);
        $this->saved = true;
    }

    public function updated(): void
    {
        $this->saved = false;
    }

    public function confirmReset(): void
    {
        Dialog::alert('Start again?', 'This wipes all your progress. Your own added words are kept.', [
            ['label' => 'Cancel', 'style' => 'cancel'],
            ['label' => 'Reset', 'style' => 'destructive'],
        ])->id(self::RESET_ALERT)->show();
    }

    #[On(ButtonPressed::class)]
    public function resetProgress(int $index, ?string $id, StudyPlanner $planner): void
    {
        if ($id !== self::RESET_ALERT) {
            return;
        }

        if ($index !== 1) {
            return;
        }

        $planner->resetProgress();

        $this->replace('/settings');
    }

    public function render(): View
    {
        return view('native.settings', [
            'mynediadOptions' => [
                StudyPlanner::MYNEDIAD_REVIEW => ['I know them — keep them fresh', "Mixed into your reviews, about 10 a day. Any you've forgotten get re-taught."],
                StudyPlanner::MYNEDIAD_LEARN => ['Teach them again', "Mynediad words come first in your {$this->newPerDay} new a day, then Sylfaen."],
                StudyPlanner::MYNEDIAD_OFF => ['Leave them out', 'Sylfaen words only.'],
            ],
        ]);
    }
}
