<?php

namespace App\NativeComponents;

use App\Models\Word;
use App\Services\StudyPlanner;
use Illuminate\View\View;
use Native\Mobile\Edge\Concerns\ValidatesInput;
use Native\Mobile\Edge\NativeComponent;

class Words extends NativeComponent
{
    use ValidatesInput;

    private const LEVELS = ['all', 'mynediad', 'sylfaen', 'own'];

    private const GENDERS = [null, 'm', 'f'];

    public string $search = '';

    public int $levelIndex = 0;

    public bool $adding = false;

    public string $cy = '';

    public string $en = '';

    public int $genderIndex = 0;

    public string $note = '';

    public bool $learnToday = true;

    public function startAdding(): void
    {
        $this->adding = true;
    }

    public function stopAdding(): void
    {
        $this->adding = false;
    }

    public function save(StudyPlanner $planner): void
    {
        $this->validate([
            'cy' => ['required', 'string', 'max:120'],
            'en' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $word = Word::query()->firstOrCreate(
            ['cy' => trim($this->cy), 'en' => trim($this->en)],
            [
                'level' => 'own',
                'theme' => 'My words',
                'gender' => self::GENDERS[$this->genderIndex] ?? null,
                'note' => trim($this->note) ?: null,
                'position' => (int) Word::query()->max('position') + 1,
            ],
        );

        if ($this->learnToday && ! $word->introduced_at) {
            $planner->introduceNow($word);
        }

        $this->cy = '';
        $this->en = '';
        $this->genderIndex = 0;
        $this->note = '';
        $this->learnToday = true;
        $this->adding = false;
    }

    public function render(): View
    {
        $level = self::LEVELS[$this->levelIndex] ?? 'all';
        $search = trim($this->search);

        $words = Word::query()
            ->with('recognise')
            ->when($level !== 'all', fn ($query) => $query->where('level', $level))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('cy', 'like', "%{$search}%")
                ->orWhere('en', 'like', "%{$search}%")))
            ->orderByRaw("case level when 'own' then 0 when 'mynediad' then 1 else 2 end")
            ->orderBy('position')
            ->limit(300)
            ->get();

        return view('native.words', ['words' => $words]);
    }
}
