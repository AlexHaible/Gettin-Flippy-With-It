<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Showing extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    public function movie(): BelongsTo {
        return $this->belongsTo(Movie::class);
    }
    public function cinema(): BelongsTo {
        return $this->belongsTo(Cinema::class);
    }
}
