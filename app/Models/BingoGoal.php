<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoGoal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function showing()
    {
        return $this->belongsTo(Showing::class);
    }
}
