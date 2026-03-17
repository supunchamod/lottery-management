<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'assistant_id',
        'date',
        'amount',
        'daily_sale_record_id',
        'status',
        'paid_amount',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'paid_at'     => 'date',
            'amount'      => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function dailySaleRecord(): BelongsTo
    {
        return $this->belongsTo(DailySaleRecord::class, 'daily_sale_record_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
