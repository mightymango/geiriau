<?php

namespace App\NativeComponents;

use App\Services\StudyPlanner;
use Geiriau\Haptics\Facades\Haptics;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Learn extends NativeComponent
{
    public int $index = 0;

    public function mount(StudyPlanner $planner): void
    {
        $firstUnseen = $planner->todaysNewWords()
            ->values()
            ->search(fn ($word) => $word->recognise?->state === 'new');

        $this->index = $firstUnseen === false ? 0 : $firstUnseen;
    }

    public function next(): void
    {
        $lastIndex = app(StudyPlanner::class)->todaysNewWords()->count() - 1;

        $this->index = min($this->index + 1, $lastIndex);

        Haptics::selection();
    }

    public function previous(): void
    {
        $this->index = max(0, $this->index - 1);

        Haptics::selection();
    }

    public function practise(): void
    {
        $this->replace('/study');
    }

    public function render(): View
    {
        $words = app(StudyPlanner::class)->todaysNewWords()->values();

        return view('native.learn', [
            'words' => $words,
            'word' => $words[$this->index] ?? null,
        ]);
    }
}
