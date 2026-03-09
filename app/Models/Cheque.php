<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'cheque_no',
        'amount',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount'   => 'decimal:2',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('due_date', '<', now()->toDateString());
    }
}
