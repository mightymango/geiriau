<?php

namespace App\NativeComponents;

use App\Models\Setting;
use App\Services\Reminders\DailyReminders;
use App\Services\StudyPlanner;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Home extends NativeComponent
{
    private const WORDS_SHOWN = 10;

    public bool $showAllWords = false;

    public function mount(DailyReminders $reminders): void
    {
        $reminders->sync();
    }

    public function toggleWords(): void
    {
        $this->showAllWords = ! $this->showAllWords;
    }

    public function meetWords(): void
    {
        $this->navigate('/learn');
    }

    public function practise(): void
    {
        $this->replace('/study');
    }

    public function learnMore(StudyPlanner $planner): void
    {
        $planner->learnMore();

        $this->navigate('/learn');
    }

    public function render(): View
    {
        $planner = app(StudyPlanner::class);
        $newWords = $planner->todaysNewWords();
        $stats = $planner->stats();

        return view('native.home', [
            'name' => Setting::read('name'),
            'today' => now(config('cymraeg.timezone'))->locale('cy')->translatedFormat('l j F'),
            'stats' => $stats,
            'newWords' => $newWords,
            'shownWords' => $this->showAllWords ? $newWords : $newWords->take(self::WORDS_SHOWN),
            'hiddenWordCount' => max(0, $newWords->count() - self::WORDS_SHOWN),
            'heldBack' => $planner->newWordsHeldBack(),
            'unseenNew' => $newWords->filter(fn ($word) => $word->recognise?->state === 'new')->count(),
            'canLearnMore' => $planner->canLearnMore(),
            'newPerDay' => $planner->newPerDay(),
            'busiestDay' => max(1, max($stats['forecast'])),
        ]);
    }
}
