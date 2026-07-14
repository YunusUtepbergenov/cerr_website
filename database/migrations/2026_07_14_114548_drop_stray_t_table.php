<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the stray "t" table: a single nullable `email` column with no model,
     * factory, or code reference anywhere — leftover from a tinker/experiment.
     */
    public function up(): void
    {
        Schema::dropIfExists('t');
    }

    /**
     * Best-effort restore of the table as it was found (single email column).
     */
    public function down(): void
    {
        Schema::create('t', function (Blueprint $table) {
            $table->text('email')->nullable();
        });
    }
};
