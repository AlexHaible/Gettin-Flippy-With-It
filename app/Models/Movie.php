<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $guarded = [];

    public function showings()
    {
        return $this->hasMany(Showing::class);
    }
}
