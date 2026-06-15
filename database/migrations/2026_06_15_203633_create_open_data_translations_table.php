<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_data_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_data_id')->constrained('open_data')->cascadeOnDelete();
            $table->string('language', 5);
            $table->string('title');
            $table->timestamps();

            $table->index(['open_data_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_data_translations');
    }
};
