<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use App\Services\CalendarImportService;

Artisan::command('calendar:import', function (CalendarImportService $importer) {
    try {
        $this->info('Starting calendar import...');
        $importer->import();
        $this->info('Calendar import completed successfully.');
    }
    catch (\Exception $e) {
        $this->error('Error importing calendar: ' . $e->getMessage());
    }
})->purpose('Fetch and parse Google Calendar events for movie nights')
    ->hourly();