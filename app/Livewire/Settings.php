<?php

namespace App\Livewire;

use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public string $name = '';

    public int $newPerDay = 5;

    public string $mynediadMode = StudyPlanner::MYNEDIAD_REVIEW;

    public bool $onboarded = false;

    public bool $reminderEnabled = true;

    public string $reminderTime = '08:00';

    public bool $confirmingReset = false;

    public bool $saved = false;

    public function mount(StudyPlanner $planner, DailyReminders $reminders): void
    {
        $this->name = (string) Setting::read('name', '');
        $this->reminderEnabled = $reminders->enabled();
        $this->reminderTime = $reminders->time();
        $this->onboarded = $planner->isOnboarded();
        $this->newPerDay = $planner->newPerDay();
        $this->mynediadMode = $planner->mynediadMode();
    }

    public function save(StudyPlanner $planner, DailyReminders $reminders)
    {
        $this->validate([
            'newPerDay' => 'required|integer|min:1|max:20',
            'mynediadMode' => 'required|in:review,learn,off',
            'reminderTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'name' => ['nullable', 'string', 'max:40'],
        ]);

        Setting::put('name', trim($this->name));

        if (! $this->onboarded) {
            $planner->onboard($this->mynediadMode, $this->newPerDay);
            Setting::put('reminder_enabled', $this->reminderEnabled ? '1' : '0');
            Setting::put('reminder_time', $this->reminderTime);
            if ($this->reminderEnabled) {
                $reminders->requestPermission(); // iOS / Android 13+ ask once
            }
            $reminders->sync();

            return $this->redirectRoute('home', navigate: true);
        }

        Setting::put('new_per_day', $this->newPerDay);
        if ($this->mynediadMode !== $planner->mynediadMode()) {
            $planner->setMynediadMode($this->mynediadMode);
        }
        $reminders->configure($this->reminderEnabled, $this->reminderTime);
        $this->saved = true;
    }

    public function wipeProgress(StudyPlanner $planner)
    {
        if (! $this->confirmingReset) {
            $this->confirmingReset = true;

            return;
        }

        $planner->resetProgress();

        return $this->redirectRoute('settings', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
