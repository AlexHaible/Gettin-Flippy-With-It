<?php

namespace App\Http\Controllers;

use App\Models\Showing;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class EntityController extends Controller
{
    public function actor(string $name): View
    {
        return $this->showingsMatching('Actor', $name, fn (Builder $query) => $query->withActor($name));
    }

    public function genre(string $name): View
    {
        return $this->showingsMatching('Genre', $name, fn (Builder $query) => $query->withGenre($name));
    }

    /**
     * @param  Closure(Builder): mixed  $movieConstraint
     */
    private function showingsMatching(string $entityType, string $name, Closure $movieConstraint): View
    {
        $showings = Showing::with(['movie', 'cinema'])
            ->whereHas('movie', $movieConstraint)
            ->orderByDesc('start_time')
            ->get();

        return view('entity', [
            'entityType' => $entityType,
            'entityName' => $name,
            'showings' => $showings,
        ]);
    }
}
