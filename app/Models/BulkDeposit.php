<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkDeposit extends Model
{
    protected $fillable = [
        'assistant_id',
        'date_from',
        'date_to',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to'   => 'date',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function statusLabel(): string
    {
        return $this->isPending() ? 'Pending' : 'Completed';
    }

    public function statusBadgeClass(): string
    {
        return $this->isPending()
            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300';
    }

    /** Number of days in the date range (inclusive). */
    public function dayCount(): int
    {
        return (int) $this->date_from->diffInDays($this->date_to) + 1;
    }
}
