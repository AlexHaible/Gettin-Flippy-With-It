<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Showing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function popcornPayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'popcorn_payer_id');
    }

    public function sodaPayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soda_payer_id');
    }

    public function ratings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
