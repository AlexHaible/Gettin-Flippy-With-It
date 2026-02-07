<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('showings', function (Blueprint $table) {
            $table->foreignId('popcorn_payer_id')->nullable()->constrained('users');
            $table->foreignId('soda_payer_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showings', function (Blueprint $table) {
            $table->dropForeign(['popcorn_payer_id']);
            $table->dropForeign(['soda_payer_id']);
            $table->dropColumn(['popcorn_payer_id', 'soda_payer_id']);
        });
    }
};