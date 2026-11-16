<?php

namespace App\Livewire;

use App\Models\Word;
use App\Services\StudyPlanner;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Words extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $level = 'all';

    public bool $adding = false;

    #[Validate('required|string|max:120')]
    public string $cy = '';

    #[Validate('required|string|max:120')]
    public string $en = '';

    #[Validate('nullable|in:m,f')]
    public ?string $gender = null;

    #[Validate('nullable|string|max:200')]
    public string $note = '';

    public bool $learnToday = true;

    public function save(StudyPlanner $planner): void
    {
        $this->validate();

        $word = Word::query()->firstOrCreate(
            ['cy' => trim($this->cy), 'en' => trim($this->en)],
            [
                'level' => 'own',
                'theme' => 'My words',
                'gender' => $this->gender ?: null,
                'note' => $this->note ?: null,
                'position' => (int) Word::query()->max('position') + 1,
            ],
        );

        if ($this->learnToday && ! $word->introduced_at) {
            $planner->introduceNow($word);
        }

        $this->reset('cy', 'en', 'gender', 'note', 'adding');
        $this->learnToday = true;
    }

    public function render()
    {
        $words = Word::query()
            ->with('recognise')
            ->when($this->level !== 'all', fn ($q) => $q->where('level', $this->level))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('cy', 'like', "%{$this->search}%")
                ->orWhere('en', 'like', "%{$this->search}%")))
            ->orderByRaw("case level when 'own' then 0 when 'mynediad' then 1 else 2 end")
            ->orderBy('position')
            ->limit(300)
            ->get();

        return view('livewire.words', ['words' => $words]);
    }
}
