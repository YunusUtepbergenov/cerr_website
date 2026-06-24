<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-time data correction for legacy news.
     *
     * News imported before status visibility was enforced carry
     * status='auto_publish' with a NULL scheduled_at. News::scopePublished()
     * can never surface that combination (a NULL scheduled_at fails the
     * scheduled_at <= now() check) and news:promote-scheduled skips it for the
     * same reason, so those rows are stranded — invisible on the public site
     * yet not genuine drafts. They were live content, so promote them to
     * 'published'. Rows with an explicit scheduled_at are left to the normal
     * scheduling flow, and drafts/disabled rows are left untouched.
     */
    public function up(): void
    {
        DB::table('news')
            ->where('status', 'auto_publish')
            ->whereNull('scheduled_at')
            ->update(['status' => 'published']);
    }

    /**
     * Irreversible: the original rows are indistinguishable from any other
     * 'published' news once corrected, so there is nothing safe to roll back.
     */
    public function down(): void
    {
        // No-op — see up() for why this correction cannot be reversed.
    }
};
