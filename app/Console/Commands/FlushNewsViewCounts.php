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

        foreach ($views as $newsId => $count) {
            $count = (int) $count;

            // Skip non-positive buffers so corrupt/zero values can never
            // decrement the lifetime counter or churn the daily totals.
            if ($count <= 0) {
                continue;
            }

            News::where('id', $newsId)->increment('view_count', $count);
            NewsDailyView::record((int) $newsId, $count);
            Redis::hincrby('news:views', $newsId, -$count);
        }

        return self::SUCCESS;
    }
}
