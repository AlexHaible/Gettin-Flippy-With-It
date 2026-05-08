<?php

namespace App\Models;

use App\Services\BingoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Showing $showing) {
            // Reload fresh relations so BingoService always has up-to-date data
            $showing->load(['movie', 'cinema', 'ratings']);
            app(BingoService::class)->evaluate($showing);
        });
    }
}
