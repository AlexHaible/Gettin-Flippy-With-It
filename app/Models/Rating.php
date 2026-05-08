<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $guarded = [];

    public function showing()
    {
        return $this->belongsTo(Showing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //
}
