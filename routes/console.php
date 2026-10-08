<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calendar:import')->everyFiveMinutes();

Schedule::command('watchlist:cleanup')->mondays()->at('00:00');
