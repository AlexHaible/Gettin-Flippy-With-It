<?php

namespace App\Models;

use App\Observers\ShowingObserver;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ShowingObserver::class)]
class Showing extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
        ];
    }

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

    /**
     * The showing starting closest to $moment, looking both backwards and forwards.
     * On an exact tie the past showing wins.
     */
    public static function closestTo(CarbonInterface $moment): ?self
    {
        $past = static::where('start_time', '<', $moment)->latest('start_time')->first();
        $upcoming = static::where('start_time', '>=', $moment)->oldest('start_time')->first();

        if ($past && $upcoming) {
            $diffPast = $moment->diffInMinutes($past->start_time, true);
            $diffUpcoming = $moment->diffInMinutes($upcoming->start_time, true);

            return $diffUpcoming < $diffPast ? $upcoming : $past;
        }

        return $past ?? $upcoming;
    }
}
