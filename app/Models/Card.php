<?php

namespace App\Models;

use App\Srs\CardState;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    public const RECOGNISE = 'recognise'; // see Welsh, recall English
    public const PRODUCE = 'produce';     // see English, recall Welsh

    public const DIRECTIONS = [self::RECOGNISE, self::PRODUCE];

    protected $fillable = ['word_id', 'direction', 'active', 'state', 'step', 'ease', 'interval', 'due_at', 'reps', 'lapses', 'last_reviewed_at'];

    protected $casts = [
        'active' => 'boolean',
        'ease' => 'float',
        'due_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function toState(): CardState
    {
        return new CardState(
            $this->state,
            $this->step,
            $this->ease,
            $this->interval,
            $this->due_at?->toDateTimeImmutable(),
            $this->reps,
            $this->lapses,
        );
    }

    public function applyState(CardState $s): void
    {
        $this->fill([
            'state' => $s->state,
            'step' => $s->step,
            'ease' => $s->ease,
            'interval' => $s->interval,
            // Store in the app timezone (UTC) so string comparisons in queries are correct.
            'due_at' => $s->due?->setTimezone(new DateTimeZone(config('app.timezone'))),
            'reps' => $s->reps,
            'lapses' => $s->lapses,
        ]);
    }
}
