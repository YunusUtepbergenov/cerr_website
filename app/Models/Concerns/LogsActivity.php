<?php

namespace App\Models\Concerns;

use App\Models\Activity;

trait LogsActivity
{
    public function logActivity(string $action, array $changes = []): void
    {
        try {
            Activity::create([
                'user_id' => auth()->id(),
                'subject_type' => static::class,
                'subject_id' => $this->getKey(),
                'action' => $action,
                'changes' => $changes ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Activity log failed: '.$e->getMessage());
        }
    }
}
