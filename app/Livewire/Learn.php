<?php

namespace App\Livewire;

use App\Services\StudyPlanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Meet today's words before being tested on them: see the word, its meaning, gender and a note,
 * say it out loud, then go to practice. (Encoding first, then retrieval straight after.)
 */
#[Layout('layouts.app')]
class Learn extends Component
{
    public int $index = 0;

    public function next(int $total): void
    {
        $this->index = min($this->index + 1, $total - 1);
    }

    public function previous(): void
    {
        $this->index = max(0, $this->index - 1);
    }

    public function render(StudyPlanner $planner)
    {
        $words = $planner->todaysNewWords()->values();

        return view('livewire.learn', [
            'words' => $words,
            'word' => $words[$this->index] ?? null,
        ]);
    }
}
