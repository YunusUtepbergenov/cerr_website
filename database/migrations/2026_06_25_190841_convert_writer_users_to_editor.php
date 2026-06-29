<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The writer role was merged into editor — a single persona now both
     * writes and edits news — so reassign any remaining writers to editor.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'writer')->update(['role' => 'editor']);
    }

    /**
     * Irreversible: the original writer/editor split cannot be reconstructed
     * once the roles are merged.
     */
    public function down(): void
    {
        //
    }
};
