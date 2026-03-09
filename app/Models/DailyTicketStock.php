<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTicketStock extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'date',
        'assistant_id',
        'lottery_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'date'     => 'date',
            'quantity' => 'integer',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }
}
