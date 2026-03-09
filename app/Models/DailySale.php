<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySale extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'assistant_id',
        'tickets_issued_val',
        'returns_qty',
        'returns_val',
        'winning_val',
        'cash_collected',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'tickets_issued_val' => 'decimal:2',
            'returns_val'        => 'decimal:2',
            'winning_val'        => 'decimal:2',
            'cash_collected'     => 'decimal:2',
            'balance'            => 'decimal:2',
        ];
    }

    /**
     * Balance = Issued Value − (Returns Value + Winnings + Cash Collected)
     * Positive  → assistant still owes the agency.
     * Negative  → agency owes the assistant.
     */
    public function computeBalance(): void
    {
        $this->balance = $this->tickets_issued_val
            - ($this->returns_val + $this->winning_val + $this->cash_collected);
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }
}
