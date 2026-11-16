<?php

namespace App\Livewire;

use App\Models\Card;
use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use App\Srs\AnswerChecker;
use App\Srs\Grade;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Study extends Component
{
    public ?int $cardId = null;

    public bool $revealed = false;

    public string $attempt = '';

    public ?string $verdict = null; // exact | close | wrong | null

    public int $doneThisSession = 0;

    public function mount(StudyPlanner $planner): void
    {
        $planner->todaysNewWords();
        $this->loadNext($planner);
    }

    public function reveal(): void
    {
        $card = $this->card();
        if ($card && $card->direction === Card::PRODUCE && trim($this->attempt) !== '') {
            $this->verdict = (new AnswerChecker)->check($this->attempt, $card->word->cy);
        }
        $this->revealed = true;
    }

    public function grade(int $grade, StudyPlanner $planner): void
    {
        $card = $this->card();
        if (! $card || ! $this->revealed) {
            return;
        }

        $planner->answer($card, Grade::from($grade));
        $this->doneThisSession++;
        $this->loadNext($planner);
    }

    public function learnMore(StudyPlanner $planner)
    {
        $planner->learnMore();

        return $this->redirectRoute('learn', navigate: true);
    }

    private function loadNext(StudyPlanner $planner): void
    {
        $this->cardId = $planner->nextCard()?->id;

        // Finished everything due: drop today's reminder so it doesn't nag.
        if ($this->cardId === null && $this->doneThisSession > 0) {
            app(DailyReminders::class)->sync();
        }
        $this->revealed = false;
        $this->attempt = '';
        $this->verdict = null;
    }

    private function card(): ?Card
    {
        return $this->cardId ? Card::with('word')->find($this->cardId) : null;
    }

    public function render(StudyPlanner $planner)
    {
        $card = $this->card();

        return view('livewire.study', [
            'card' => $card,
            'word' => $card?->word,
            'previews' => $card && $this->revealed ? $planner->preview($card) : [],
            'grades' => Grade::cases(),
            'remaining' => $planner->stats()['due'],
            'canLearnMore' => ! $card && $planner->canLearnMore(),
            'newPerDay' => $planner->newPerDay(),
        ]);
    }
}
