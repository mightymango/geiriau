<?php

namespace App\Livewire;

use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public function mount(StudyPlanner $planner, DailyReminders $reminders)
    {
        if (! $planner->isOnboarded()) {
            return $this->redirectRoute('settings', navigate: true);
        }

        // Opening the app refreshes the week of reminders ahead.
        $reminders->sync();
    }

    public function render(StudyPlanner $planner)
    {
        $newWords = $planner->todaysNewWords();

        return view('livewire.home', [
            'stats' => $planner->stats(),
            'newWords' => $newWords,
            'heldBack' => $planner->newWordsHeldBack(),
            'unseenNew' => $newWords->filter(fn ($w) => $w->recognise?->state === 'new')->count(),
        ]);
    }
}
