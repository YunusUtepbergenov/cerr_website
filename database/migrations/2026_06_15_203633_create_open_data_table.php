<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_mime')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_data');
    }
};
