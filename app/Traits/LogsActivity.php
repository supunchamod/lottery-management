<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Attach this trait to any Eloquent model to automatically record
 * created / updated / deleted events in the activity_logs table.
 *
 * Timestamp-only changes (created_at, updated_at) are silently skipped.
 * Calls to saveQuietly() bypass all Eloquent events and are NOT logged.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::record('created', $model, [], $model->toArray());
        });

        static::updated(function ($model) {
            $excluded = ['created_at', 'updated_at', 'deleted_at'];
            $dirty    = array_diff_key($model->getDirty(), array_flip($excluded));

            if (empty($dirty)) {
                return; // Only timestamps changed — skip.
            }

            $old = array_intersect_key($model->getOriginal(), $dirty);
            ActivityLog::record('updated', $model, $old, $dirty);
        });

        static::deleted(function ($model) {
            ActivityLog::record('deleted', $model, $model->toArray(), []);
        });
    }
}
