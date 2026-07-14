<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\NewsDailyView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FlushNewsViewCounts extends Command
{
    protected $signature = 'news:flush-views';

    protected $description = 'Flush buffered news view counts from Redis to the database';

    public function handle(): int
    {
        $views = Redis::hgetall('news:views');

        if (empty($views)) {
            return self::SUCCESS;
        }

        // Resolve which buffered ids still map to a live article. A buffer left
        // over from a deleted article (or an id shifted by a schema rebuild)
        // would otherwise raise a foreign-key violation in NewsDailyView::record()
        // and abort the whole flush, permanently stalling view analytics.
        $existingIds = News::whereIn('id', array_keys($views))
            ->pluck('id')
            ->flip();

        foreach ($views as $newsId => $count) {
            $newsId = (int) $newsId;
            $count = (int) $count;

            // Skip non-positive buffers so corrupt/zero values can never
            // decrement the lifetime counter or churn the daily totals.
            if ($count <= 0) {
                continue;
            }

            // Orphaned buffer for an article that no longer exists: discard it
            // so it stops accumulating and can never crash a future flush.
            if (! $existingIds->has($newsId)) {
                Redis::hdel('news:views', $newsId);

                continue;
            }

            News::where('id', $newsId)->increment('view_count', $count);
            NewsDailyView::record($newsId, $count);
            Redis::hincrby('news:views', $newsId, -$count);
        }

        return self::SUCCESS;
    }
}
