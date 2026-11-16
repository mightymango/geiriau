<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Word extends Model
{
    protected $fillable = ['cy', 'en', 'level', 'theme', 'pos', 'gender', 'note', 'position', 'introduced_at', 'intro_day'];

    protected $casts = ['introduced_at' => 'datetime'];

    protected static function booted(): void
    {
        static::created(function (Word $word) {
            foreach (Card::DIRECTIONS as $direction) {
                $word->cards()->create(['direction' => $direction]);
            }
        });
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function recognise(): HasOne
    {
        return $this->hasOne(Card::class)->where('direction', Card::RECOGNISE);
    }

    public function produce(): HasOne
    {
        return $this->hasOne(Card::class)->where('direction', Card::PRODUCE);
    }

    public function genderLabel(): ?string
    {
        return match ($this->gender) {
            'f' => 'eb', // enw benywaidd — feminine noun
            'm' => 'eg', // enw gwrywaidd — masculine noun
            default => null,
        };
    }
}
