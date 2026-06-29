<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NewsDailyView extends Model
{
    protected $fillable = [
        'news_id',
        'date',
        'views',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'views' => 'integer',
        ];
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Accumulate a batch of views for an article on a given day. Called by the
     * scheduled flush, so the per-day total is built up incrementally rather
     * than counted per request.
     */
    public static function record(int $newsId, int $count, Carbon|string|null $date = null): void
    {
        if ($count <= 0) {
            return;
        }

        $date = $date instanceof Carbon ? $date->toDateString() : ($date ?? now()->toDateString());
        $now = now()->toDateTimeString();

        // Atomic accumulate: a single INSERT .. ON CONFLICT keeps the per-day
        // total correct even if two flushes for the same article/day overlap.
        // The bare `views` on the right refers to the existing row on both
        // Postgres and SQLite; `excluded` is the row we tried to insert.
        DB::statement(
            'INSERT INTO news_daily_views (news_id, date, views, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)
             ON CONFLICT (news_id, date) DO UPDATE SET views = news_daily_views.views + excluded.views, updated_at = excluded.updated_at',
            [$newsId, $date, $count, $now, $now],
        );
    }
}
