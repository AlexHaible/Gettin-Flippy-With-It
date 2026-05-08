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
        Schema::create('bingo_goals', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year');
            $table->smallInteger('position'); // 1-25
            $table->string('type');           // e.g. 'genre', 'runtime', 'actor', 'cinema', 'mutual_liked', 'mutual_disliked', 'free_square'
            $table->string('target_value')->nullable(); // e.g. 'Action', '180', 'Ryan Gosling'
            $table->string('title');          // Human-readable label shown on the card
            $table->boolean('is_completed')->default(false);
            $table->foreignId('showing_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['year', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bingo_goals');
    }
};
