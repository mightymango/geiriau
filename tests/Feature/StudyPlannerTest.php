<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\Word;
use App\Services\StudyPlanner;
use App\Srs\Grade;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyPlannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));
    }

    public function test_word_bank_is_loaded_by_migrations(): void
    {
        $this->assertGreaterThan(400, Word::count());
        $this->assertSame(Word::count() * 2, Card::count());
    }

    public function test_first_launch_shows_settings(): void
    {
        $this->get('/settings')->assertOk()->assertSee('Croeso');
    }

    public function test_five_new_sylfaen_words_a_day(): void
    {
        $planner = app(StudyPlanner::class);
        $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);

        $today = $planner->todaysNewWords();
        $this->assertCount(5, $today);
        $this->assertSame(['sylfaen'], $today->pluck('level')->unique()->values()->all());

        // Asking again on the same day doesn't add more.
        $this->assertCount(5, $planner->todaysNewWords());

        $this->travelTo(CarbonImmutable::parse('2026-11-17 10:00:00'));
        $this->assertCount(5, $planner->todaysNewWords());
        $this->assertSame(10, Word::whereNotNull('introduced_at')->count());
    }

    public function test_recognising_a_word_unlocks_production_next_day(): void
    {
        $planner = app(StudyPlanner::class);
        $planner->onboard(StudyPlanner::MYNEDIAD_OFF, 5);
        $planner->todaysNewWords();

        $card = $planner->nextCard();
        $this->assertSame(Card::RECOGNISE, $card->direction);
        $this->assertSame('new', $card->state);

        $planner->answer($card, Grade::Good);           // → 10 min step
        $planner->answer($card->fresh(), Grade::Good);  // → graduates to 1 day

        $this->assertSame('review', $card->fresh()->state);

        $produce = Card::where('word_id', $card->word_id)->where('direction', Card::PRODUCE)->first();
        $this->assertTrue($produce->active);
        $this->assertSame('learning', $produce->state);
        $this->assertTrue($produce->due_at->greaterThan(now()));
    }

    public function test_mynediad_as_known_schedules_reviews_gradually(): void
    {
        $planner = app(StudyPlanner::class);
        $planner->onboard(StudyPlanner::MYNEDIAD_REVIEW, 5);

        $mynediad = Card::whereHas('word', fn ($q) => $q->where('level', 'mynediad'));
        $this->assertSame(Word::where('level', 'mynediad')->count() * 2, (clone $mynediad)->where('active', true)->count());

        // Only the first day's batch is due today; the rest are spread out.
        $dueNow = (clone $mynediad)->where('due_at', '<=', now())->count();
        $this->assertSame(config('cymraeg.mynediad_per_day'), $dueNow);
    }
}
