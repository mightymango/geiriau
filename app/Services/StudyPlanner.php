<?php

namespace App\Services;

use App\Models\Card;
use App\Models\ReviewLog;
use App\Models\Setting;
use App\Models\Word;
use App\Srs\CardState;
use App\Srs\Grade;
use App\Srs\Scheduler;
use App\Srs\StudyDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudyPlanner
{
    public const MYNEDIAD_REVIEW = 'review'; // already known: check them, don't "teach" them
    public const MYNEDIAD_LEARN = 'learn';   // teach them as new words, before Sylfaen
    public const MYNEDIAD_OFF = 'off';

    public function __construct(
        private Scheduler $scheduler,
        private StudyDay $day,
    ) {}

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    /** Dates go to the database in the app timezone (UTC) so comparisons line up. */
    private function utc(\DateTimeImmutable $time): CarbonImmutable
    {
        return CarbonImmutable::instance($time)->setTimezone(config('app.timezone'));
    }

    public function todayKey(): string
    {
        return $this->day->key($this->now());
    }

    public function isOnboarded(): bool
    {
        return Setting::read('onboarded') === '1';
    }

    public function newPerDay(): int
    {
        return (int) Setting::read('new_per_day', config('cymraeg.new_per_day', 5));
    }

    public function mynediadMode(): string
    {
        return Setting::read('mynediad_mode', self::MYNEDIAD_REVIEW);
    }

    public function onboard(string $mynediadMode, int $newPerDay): void
    {
        Setting::put('new_per_day', max(1, min(20, $newPerDay)));
        $this->setMynediadMode($mynediadMode);
        Setting::put('onboarded', '1');
    }

    public function setMynediadMode(string $mode): void
    {
        Setting::put('mynediad_mode', $mode);

        if ($mode === self::MYNEDIAD_REVIEW) {
            $this->placeMynediadAsKnown();
        }
    }

    /**
     * Words picked for today. Introduces the day's batch the first time it's called each study day.
     */
    public function todaysNewWords(): Collection
    {
        if (! $this->isOnboarded()) {
            return collect();
        }

        $key = $this->todayKey();

        if (Setting::read('intro_day') !== $key) {
            $this->introduceBatch($key);
        }

        return Word::query()->where('intro_day', $key)->orderBy('introduced_at')->orderBy('position')->get();
    }

    public function newWordsHeldBack(): bool
    {
        return Setting::read('held_back_day') === $this->todayKey();
    }

    private function introduceBatch(string $key): void
    {
        Setting::put('intro_day', $key);

        // Too many reviews waiting? Clear the backlog before adding more.
        if ($this->dueReviewCount() > (int) config('cymraeg.backlog_limit', 80)) {
            Setting::put('held_back_day', $key);

            return;
        }

        $levels = $this->mynediadMode() === self::MYNEDIAD_LEARN
            ? ['own', 'mynediad', 'sylfaen']
            : ['own', 'sylfaen'];

        $words = Word::query()
            ->whereNull('introduced_at')
            ->whereIn('level', $levels)
            ->orderByRaw("case level when 'own' then 0 when 'mynediad' then 1 else 2 end")
            ->orderBy('position')
            ->limit($this->newPerDay())
            ->get();

        $this->introduce($words, $key);
    }

    /** Add a single word to today's batch (used when you add your own word). */
    public function introduceNow(Word $word): void
    {
        $this->introduce(collect([$word]), $this->todayKey());
    }

    private function introduce(Collection $words, string $key): void
    {
        $now = $this->now();

        DB::transaction(function () use ($words, $key, $now) {
            foreach ($words as $word) {
                $word->update(['introduced_at' => $now, 'intro_day' => $key]);
                $word->recognise()->update([
                    'active' => true, 'state' => CardState::NEW, 'step' => 0, 'due_at' => $now,
                ]);
            }
        });
    }

    /**
     * Treat Mynediad as already learned: schedule both directions as reviews, spread over the
     * coming days so they resurface gradually. Anything forgotten drops back into relearning.
     */
    public function placeMynediadAsKnown(): void
    {
        $now = $this->now();
        $perDay = (int) config('cymraeg.mynediad_per_day', 10);

        $words = Word::query()->where('level', 'mynediad')->whereNull('introduced_at')
            ->orderBy('position')->get();

        DB::transaction(function () use ($words, $now, $perDay) {
            foreach ($words->values() as $i => $word) {
                $offset = intdiv($i, $perDay);
                $word->update(['introduced_at' => $now]);

                $word->recognise()->update([
                    'active' => true, 'state' => CardState::REVIEW, 'interval' => 4,
                    'due_at' => $this->utc($this->day->startPlus($now, $offset)),
                ]);
                $word->produce()->update([
                    'active' => true, 'state' => CardState::REVIEW, 'interval' => 3,
                    'due_at' => $this->utc($this->day->startPlus($now, $offset + 2)),
                ]);
            }
        });
    }

    /**
     * Next card to show: cards mid-learning that are due → today's unseen new cards → due reviews
     * (shuffled, both directions mixed) → learning cards due soon (so you're not left waiting).
     */
    public function nextCard(): ?Card
    {
        $now = $this->now();
        $base = fn () => Card::query()->active()->with('word');

        return $base()->whereIn('state', [CardState::LEARNING, CardState::RELEARNING])
            ->where('due_at', '<=', $now)->orderBy('due_at')->first()
            ?? $base()->where('state', CardState::NEW)->orderBy('due_at')->orderBy('id')->first()
            ?? $base()->where('state', CardState::REVIEW)->where('due_at', '<=', $now)->inRandomOrder()->first()
            ?? $base()->whereIn('state', [CardState::LEARNING, CardState::RELEARNING])
                ->where('due_at', '<=', $now->addMinutes((int) config('cymraeg.learn_ahead_minutes', 20)))
                ->orderBy('due_at')->first();
    }

    public function answer(Card $card, Grade $grade): void
    {
        $now = $this->now();
        $before = $card->toState();
        $after = $this->scheduler->answer($before, $grade, $now);

        DB::transaction(function () use ($card, $grade, $before, $after, $now) {
            ReviewLog::create([
                'card_id' => $card->id,
                'grade' => $grade->value,
                'state_before' => $before->state,
                'interval_before' => $before->interval,
                'interval_after' => $after->interval,
                'day' => $this->day->key($now),
                'reviewed_at' => $now,
            ]);

            $card->applyState($after);
            $card->last_reviewed_at = $now;
            $card->save();

            // Production (English → Welsh) is harder, so it unlocks the day after you first
            // recognise the word — a deliberate gap that makes the first recall effortful.
            $graduated = $card->direction === Card::RECOGNISE
                && $before->isLearning() && $after->state === CardState::REVIEW;

            if ($graduated) {
                Card::query()->where('word_id', $card->word_id)
                    ->where('direction', Card::PRODUCE)->where('active', false)
                    ->update([
                        'active' => true,
                        'state' => CardState::LEARNING,
                        'step' => 0,
                        'due_at' => $this->utc($this->day->startPlus($now, 1)),
                    ]);
            }
        });
    }

    /** @return array<int, string> */
    public function preview(Card $card): array
    {
        return $this->scheduler->preview($card->toState(), $this->now());
    }

    public function dueReviewCount(): int
    {
        return Card::query()->active()->where('state', CardState::REVIEW)
            ->where('due_at', '<=', $this->now())->count();
    }

    public function stats(): array
    {
        $now = $this->now();
        $today = $this->todayKey();
        $endOfToday = $this->utc($this->day->startPlus($now, 1));

        $learning = Card::query()->active()
            ->whereIn('state', [CardState::NEW, CardState::LEARNING, CardState::RELEARNING])
            ->where('due_at', '<=', $endOfToday)->count();

        $known = Card::query()->active()->where('direction', Card::RECOGNISE)
            ->where('state', CardState::REVIEW);

        $forecast = [];
        for ($d = 1; $d <= 7; $d++) {
            $forecast[] = Card::query()->active()
                ->where('due_at', '>=', $this->utc($this->day->startPlus($now, $d)))
                ->where('due_at', '<', $this->utc($this->day->startPlus($now, $d + 1)))
                ->count();
        }

        return [
            'due' => $this->dueReviewCount() + $learning,
            'reviewed_today' => ReviewLog::query()->where('day', $today)->count(),
            'streak' => $this->streak(),
            'known' => (clone $known)->count(),
            'mature' => (clone $known)->where('interval', '>=', 21)->count(),
            'by_level' => Word::query()->whereNotNull('introduced_at')
                ->selectRaw('level, count(*) as total')->groupBy('level')->pluck('total', 'level')->all(),
            'remaining' => Word::query()->whereNull('introduced_at')->count(),
            'forecast' => $forecast,
        ];
    }

    private function streak(): int
    {
        $days = ReviewLog::query()->distinct()->orderByDesc('day')->limit(400)->pluck('day')->all();
        if (! $days) {
            return 0;
        }

        $now = $this->now();
        $cursor = $this->day->key($now);
        // A streak survives until the end of today even if you haven't studied yet.
        if ($days[0] !== $cursor) {
            $cursor = $this->day->key($now->subDay());
        }

        $streak = 0;
        foreach ($days as $day) {
            if ($day !== $cursor) {
                break;
            }
            $streak++;
            $cursor = CarbonImmutable::parse($cursor)->subDay()->format('Y-m-d');
        }

        return $streak;
    }

    public function resetProgress(): void
    {
        DB::transaction(function () {
            ReviewLog::query()->delete();
            Card::query()->update([
                'active' => false, 'state' => CardState::NEW, 'step' => 0, 'ease' => 2.5,
                'interval' => 0, 'due_at' => null, 'reps' => 0, 'lapses' => 0, 'last_reviewed_at' => null,
            ]);
            Word::query()->update(['introduced_at' => null, 'intro_day' => null]);
            Setting::query()->whereIn('key', ['intro_day', 'held_back_day', 'onboarded'])->delete();
        });
    }
}
