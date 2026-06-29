<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The viewer role was removed; every user is now staff. Point the column
     * default at editor so a user created without an explicit role still lands
     * on a recognised role. Existing 'viewer' rows are intentionally left as-is
     * (they remain access-less) rather than promoted into a staff role.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('editor')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('viewer')->change();
        });
    }
};
