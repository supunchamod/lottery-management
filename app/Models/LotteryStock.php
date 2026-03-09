<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryStock extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'date',
        'agent_id',
        'lottery_id',
        'qty_issued',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'agent_id');
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }
}
