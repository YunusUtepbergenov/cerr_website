<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proactive indexes on the two growing news tables. PostgreSQL does not
     * auto-index foreign keys, so both hot access patterns currently seq-scan:
     * - news_translations(news_id, lang): every whereHas('translation') EXISTS
     *   join and with('translation') eager load filters on these two columns.
     * - news(category_id, created_at): the homepage per-category sections and
     *   the category listing filter category_id then ORDER BY created_at DESC.
     * Harmless at today's row counts; keeps queries flat as content grows.
     */
    public function up(): void
    {
        Schema::table('news_translations', function (Blueprint $table) {
            $table->index(['news_id', 'lang'], 'news_translations_news_id_lang_idx');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index(['category_id', 'created_at'], 'news_category_id_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_translations', function (Blueprint $table) {
            $table->dropIndex('news_translations_news_id_lang_idx');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('news_category_id_created_at_idx');
        });
    }
};
