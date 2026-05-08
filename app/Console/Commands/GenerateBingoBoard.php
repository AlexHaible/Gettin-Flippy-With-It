<?php

namespace App\Console\Commands;

use App\Models\BingoGoal;
use App\Models\Cinema;
use App\Models\Movie;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateBingoBoard extends Command
{
    protected $signature = 'bingo:generate {year? : The year to generate for (defaults to current year)} {--force : Overwrite existing board}';

    protected $description = 'Generate the 25 Cinema Bingo goals for the given year';

    public function handle(): void
    {
        $year = (int) ($this->argument('year') ?? now()->year);

        if (BingoGoal::where('year', $year)->exists()) {
            if (! $this->option('force')) {
                $this->error("A bingo board for {$year} already exists. Use --force to regenerate.");

                return;
            }
            BingoGoal::where('year', $year)->delete();
            $this->info("Deleted existing board for {$year}.");
        }

        $goals = $this->buildGoals();

        foreach ($goals as $i => $goal) {
            BingoGoal::create([
                'year'         => $year,
                'position'     => $i + 1,
                'type'         => $goal['type'],
                'target_value' => $goal['target_value'] ?? null,
                'title'        => $goal['title'],
                // Free square is always pre-completed
                'is_completed' => $goal['type'] === 'free_square',
            ]);
        }

        $this->info("✅ Generated {$goals->count()} bingo goals for {$year}!");
        $this->table(['Pos', 'Title', 'Type'], $goals->map(fn ($g, $i) => [$i + 1, $g['title'], $g['type']]));
    }

    protected function buildGoals(): Collection
    {
        // ── Static goals (always present) ──────────────────────────────────
        $static = collect([
            ['type' => 'runtime',         'target_value' => '180', 'title' => 'Epic Marathon (3+ hours)'],
            ['type' => 'mutual_liked',    'target_value' => null,  'title' => 'Unanimous Masterpiece'],
            ['type' => 'mutual_disliked', 'target_value' => null,  'title' => 'Mutual Regret'],
            ['type' => 'runtime',         'target_value' => '150', 'title' => 'Long Night (2.5+ hours)'],
        ]);

        // ── Genre goals (pull from movies we already know about) ──────────
        $knownGenres = Movie::whereNotNull('genres')
            ->get()
            ->flatMap(fn ($m) => json_decode($m->genres, true) ?? [])
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(8);

        $genreGoals = $knownGenres->map(fn ($genre) => [
            'type' => 'genre',
            'target_value' => $genre,
            'title' => "See a {$genre} film",
        ]);

        // ── Actor goals (top actors from your history) ─────────────────────
        $knownActors = Movie::whereNotNull('cast')
            ->get()
            ->flatMap(fn ($m) => json_decode($m->cast, true) ?? [])
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(6);

        $actorGoals = $knownActors->map(fn ($actor) => [
            'type' => 'actor',
            'target_value' => $actor,
            'title' => "See a film with {$actor}",
        ]);

        // ── Cinema goals (cinemas you visit) ─────────────────────────────
        $cinemaGoals = Cinema::inRandomOrder()->take(4)->get()->map(fn ($c) => [
            'type' => 'cinema',
            'target_value' => $c->name,
            'title' => "Visit {$c->name}",
        ]);

        // ── Merge, shuffle, and trim to exactly 24 (position 13 is reserved) ─
        $all = $static
            ->merge($genreGoals)
            ->merge($actorGoals)
            ->merge($cinemaGoals)
            ->shuffle()
            ->values();

        // Pad to 24 if short
        while ($all->count() < 24) {
            $extra = $knownGenres->random();
            $all->push(['type' => 'genre', 'target_value' => $extra, 'title' => "See another {$extra} film"]);
        }

        // Splice the FREE SQUARE into the exact center (index 12 = position 13)
        $first24 = $all->take(24)->values();
        $freeSquare = ['type' => 'free_square', 'target_value' => null, 'title' => '🍿 FREE SQUARE 🍿'];
        $result = $first24->slice(0, 12)
            ->push($freeSquare)
            ->merge($first24->slice(12))
            ->values();

        return $result;
    }
}
