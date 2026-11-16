<?php

namespace App\Livewire;

use App\Models\Card;
use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
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
            $this->verdict = $this->check($this->attempt, $card->word->cy);
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

    /** Typed answers: exact match, or "close" when only accents/case/punctuation differ. */
    private function check(string $typed, string $answer): string
    {
        $norm = fn (string $s) => preg_replace('/[^\p{L}\p{N} ]/u', '', mb_strtolower(trim($s)));
        $strip = fn (string $s) => strtr($s, [
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u', 'ŵ' => 'w', 'ŷ' => 'y',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u', 'á' => 'a', 'é' => 'e', 'à' => 'a', 'è' => 'e',
        ]);

        // Accept any one of the alternatives separated by "/" (e.g. "eisiau / moyn").
        foreach (array_map('trim', explode('/', $answer)) as $option) {
            if ($norm($typed) === $norm($option)) {
                return 'exact';
            }
            if ($strip($norm($typed)) === $strip($norm($option))) {
                return 'close';
            }
        }

        return 'wrong';
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
        ]);
    }
}
