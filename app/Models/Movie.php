<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'cast' => 'array',
        ];
    }

    public function showings(): HasMany
    {
        return $this->hasMany(Showing::class);
    }

    /**
     * Movies whose `genres` JSON array contains $name.
     */
    public function scopeWithGenre(Builder $query, string $name): void
    {
        $this->whereJsonArrayContains($query, 'genres', $name);
    }

    /**
     * Movies whose `cast` JSON array contains $name.
     */
    public function scopeWithActor(Builder $query, string $name): void
    {
        $this->whereJsonArrayContains($query, 'cast', $name);
    }

    /**
     * The column holds a JSON array of strings, so a LIKE on the quoted
     * name is enough for exact name matches.
     */
    private function whereJsonArrayContains(Builder $query, string $column, string $name): void
    {
        $query->where($column, 'LIKE', '%"'.$name.'"%');
    }
}
