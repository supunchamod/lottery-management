<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistant_id',
        'date',
        'type',
        'amount',
        'running_balance',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'date',
            'amount'          => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }
}
