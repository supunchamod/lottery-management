<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamWinning extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'assistant_id',
        'date',
        'ticket_barcode',
        'reported_winning_value',
        'actual_winning_value',
        'difference',
        'is_paid_back',
        'paid_back_amount',
        'paid_back_date',
        'daily_sale_record_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'                   => 'date',
            'paid_back_date'         => 'date',
            'reported_winning_value' => 'decimal:2',
            'actual_winning_value'   => 'decimal:2',
            'difference'             => 'decimal:2',
            'paid_back_amount'       => 'decimal:2',
            'is_paid_back'           => 'boolean',
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

    /** Remaining amount the assistant still owes. */
    public function remainingAmount(): float
    {
        return max(0, (float) $this->difference - (float) $this->paid_back_amount);
    }
}
