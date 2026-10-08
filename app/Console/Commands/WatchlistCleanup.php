<?php

namespace App\Console\Commands;

use App\Models\WatchlistMovie;
use Illuminate\Console\Command;

class WatchlistCleanup extends Command
{
    protected $signature = 'watchlist:cleanup';

    protected $description = 'Remove movies that have already premiered from the watchlist';

    public function handle(): int
    {
        $this->info('Starting watchlist cleanup...');

        $count = WatchlistMovie::whereNotNull('release_date')
            ->where('release_date', '<', today())
            ->delete();

        $this->info("Watchlist cleanup completed. Removed {$count} released movies.");

        return self::SUCCESS;
    }
}
