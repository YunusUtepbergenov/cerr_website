<?php

namespace App\Console\Commands;

use App\Models\News;
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
            News::where('id', $newsId)->increment('view_count', (int) $count);
            Redis::hincrby('news:views', $newsId, -(int) $count);
        }

        return self::SUCCESS;
    }
}
