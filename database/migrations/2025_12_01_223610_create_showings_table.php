<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('showings', function (Blueprint $table) {
            $table->id();

            // Foreign Keys with constraints to maintain referential integrity.
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The main booker (usually You)
            $table->foreignId('cinema_id')->constrained();
            $table->foreignId('movie_id')->constrained();

            // Unique ID from Google to prevent importing the same event twice if the job runs multiple times.
            $table->string('google_event_id')->unique();

            $table->dateTime('start_time');

            // Hall info (e.g., "Sal 3", "IMAX") is stored as a string as it varies wildly between venues.
            $table->string('hall_name')->nullable();

            // Booking ref for quick lookup if needed manually.
            $table->string('booking_reference')->nullable();

            // SEAT DATA STORAGE:
            // We store seats as a normalized comma-separated string (e.g., "C1,C2").
            // This choice avoids a complex 'seats' pivot table, as we don't need to query individual seats often,
            // only parse them for the Heatmap visualization.
            $table->string('seat_numbers')->nullable();

            // FINANCIALS:
            // Always store currency as integers (lowest unit) to prevent floating-point math errors.
            // Example: 300.00 DKK is stored as 30000.
            $table->integer('price_total')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showings');
    }
};
