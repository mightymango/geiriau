<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['card_id', 'grade', 'state_before', 'interval_before', 'interval_after', 'day', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];
}
