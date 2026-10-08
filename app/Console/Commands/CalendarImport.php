<?php

namespace App\Console\Commands;

use App\Services\CalendarImportService;
use Exception;
use Illuminate\Console\Command;

class CalendarImport extends Command
{
    protected $signature = 'calendar:import';

    protected $description = 'Fetch and parse Google Calendar events for movie nights';

    public function handle(): int
    {
        try {
            $this->info('Starting calendar import...');

            // Resolved inside the try block: building the importer's dependencies
            // (e.g. the LLM client) can fail on missing configuration.
            $this->laravel->make(CalendarImportService::class)->import();

            $this->info('Calendar import completed successfully.');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Error importing calendar: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
