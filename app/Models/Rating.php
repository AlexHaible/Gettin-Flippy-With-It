<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    /** Allowed values of the `score` column (see the ratings migration). */
    public const SCORES = ['liked', 'meh', 'disliked'];

    protected $guarded = [];

    public function showing(): BelongsTo
    {
        return $this->belongsTo(Showing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
