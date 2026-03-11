<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultDistribution extends Model
{
    protected $fillable = [
        'assistant_id',
        'lottery_id',
        'day_of_week',
        'default_qty',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'default_qty' => 'integer',
    ];

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class);
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    /**
     * Return a nested array [ assistant_id ][ lottery_id ] => default_qty
     * for the given day-of-week (0=Sun … 6=Sat).
     */
    public static function gridForDay(int $dayOfWeek): array
    {
        $grid = [];
        static::where('day_of_week', $dayOfWeek)
              ->where('default_qty', '>', 0)
              ->get(['assistant_id', 'lottery_id', 'default_qty'])
              ->each(function ($r) use (&$grid) {
                  $grid[$r->assistant_id][$r->lottery_id] = $r->default_qty;
              });

        return $grid;
    }
}
