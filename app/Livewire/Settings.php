<?php

namespace App\Livewire;

use App\Services\StudyPlanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public int $newPerDay = 5;

    public string $mynediadMode = StudyPlanner::MYNEDIAD_REVIEW;

    public bool $onboarded = false;

    public bool $confirmingReset = false;

    public bool $saved = false;

    public function mount(StudyPlanner $planner): void
    {
        $this->onboarded = $planner->isOnboarded();
        $this->newPerDay = $planner->newPerDay();
        $this->mynediadMode = $planner->mynediadMode();
    }

    public function save(StudyPlanner $planner)
    {
        $this->validate([
            'newPerDay' => 'required|integer|min:1|max:20',
            'mynediadMode' => 'required|in:review,learn,off',
        ]);

        if (! $this->onboarded) {
            $planner->onboard($this->mynediadMode, $this->newPerDay);

            return $this->redirectRoute('home', navigate: true);
        }

        \App\Models\Setting::put('new_per_day', $this->newPerDay);
        if ($this->mynediadMode !== $planner->mynediadMode()) {
            $planner->setMynediadMode($this->mynediadMode);
        }
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
