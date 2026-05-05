<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

class PromoteScheduledNews extends Command
{
    protected $signature = 'news:promote-scheduled';

    protected $description = 'Flip auto_publish news whose scheduled_at has passed to published.';

    public function handle(): int
    {
        $count = News::where('status', 'auto_publish')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->update(['status' => 'published']);

        $this->info("Promoted {$count} news item(s) from auto_publish to published.");

        return self::SUCCESS;
    }
}
