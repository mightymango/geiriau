<?php

namespace App\NativeComponents;

use App\Models\Card;
use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use App\Srs\AnswerChecker;
use App\Srs\Grade;
use Geiriau\Haptics\Facades\Haptics;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Study extends NativeComponent
{
    public ?int $cardId = null;

    public bool $revealed = false;

    public string $attempt = '';

    public ?string $verdict = null;

    public int $doneThisSession = 0;

    public function mount(StudyPlanner $planner): void
    {
        $planner->todaysNewWords();

        $this->loadNext($planner);
    }

    public function reveal(?string $submitted = null): void
    {
        $card = $this->card();

        if (! $card) {
            return;
        }

        $this->attempt = $submitted ?? $this->attempt;

        if ($card->direction === Card::PRODUCE && trim($this->attempt) !== '') {
            $this->verdict = (new AnswerChecker)->check($this->attempt, $card->word->cy);

            match ($this->verdict) {
                'exact' => Haptics::success(),
                'close' => Haptics::warning(),
                'wrong' => Haptics::error(),
            };
        }

        $this->revealed = true;
    }

    public function grade(int $grade, StudyPlanner $planner, DailyReminders $reminders): void
    {
        $card = $this->card();

        if (! $card) {
            return;
        }

        if (! $this->revealed) {
            return;
        }

        $planner->answer($card, Grade::from($grade));
        $this->doneThisSession++;
        $this->loadNext($planner);

        if ($this->cardId !== null) {
            Haptics::impact();

            return;
        }

        Haptics::success();
        $reminders->sync();
    }

    public function learnMore(StudyPlanner $planner): void
    {
        $planner->learnMore();

        $this->navigate('/learn');
    }

    public function goHome(): void
    {
        $this->replace('/');
    }

    private function loadNext(StudyPlanner $planner): void
    {
        $this->cardId = $planner->nextCard()?->id;
        $this->revealed = false;
        $this->attempt = '';
        $this->verdict = null;
    }

    private function card(): ?Card
    {
        if ($this->cardId === null) {
            return null;
        }

        return Card::query()->with('word')->find($this->cardId);
    }

    public function render(): View
    {
        $planner = app(StudyPlanner::class);
        $card = $this->card();

        return view('native.study', [
            'card' => $card,
            'word' => $card?->word,
            'produce' => $card?->direction === Card::PRODUCE,
            'previews' => $card && $this->revealed ? $planner->preview($card) : [],
            'grades' => Grade::cases(),
            'remaining' => $planner->stats()['due'],
            'canLearnMore' => ! $card && $planner->canLearnMore(),
            'newPerDay' => $planner->newPerDay(),
        ]);
    }
}
