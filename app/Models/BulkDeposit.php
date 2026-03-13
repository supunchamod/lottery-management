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
        // Summary totals
        'total_qty',
        'unit_price',
        'total_value',
        // Cash denominations
        'denom_5', 'denom_10', 'denom_20', 'denom_50',
        'denom_100', 'denom_500', 'denom_1000', 'denom_5000',
        'total_cash',
        // Winnings
        'nlb_winning', 'dlb_winning', 'tw_winning', 'total_winning',
        'total_cw',
        // Meta
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_from'     => 'date',
            'date_to'       => 'date',
            'unit_price'    => 'decimal:2',
            'total_value'   => 'decimal:2',
            'total_cash'    => 'decimal:2',
            'nlb_winning'   => 'decimal:2',
            'dlb_winning'   => 'decimal:2',
            'tw_winning'    => 'decimal:2',
            'total_winning' => 'decimal:2',
            'total_cw'      => 'decimal:2',
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

    // ── Computed ──────────────────────────────────────────────────────────────

    /** Recalculate and store all derived fields from raw inputs. */
    public function compute(): void
    {
        $this->total_value   = $this->total_qty * $this->unit_price;
        $this->total_cash    = ($this->denom_5    * 5)
                             + ($this->denom_10   * 10)
                             + ($this->denom_20   * 20)
                             + ($this->denom_50   * 50)
                             + ($this->denom_100  * 100)
                             + ($this->denom_500  * 500)
                             + ($this->denom_1000 * 1000)
                             + ($this->denom_5000 * 5000);
        $this->total_winning = $this->nlb_winning + $this->dlb_winning + $this->tw_winning;
        $this->total_cw      = $this->total_cash + $this->total_winning;
    }

    /** Outstanding balance (positive = assistant still owes). */
    public function autoBalance(): float
    {
        return (float) $this->total_value - (float) $this->total_cw;
    }

    /** 'Paid' when cash+winnings covers the total value, otherwise 'Balance'. */
    public function autoStatus(): string
    {
        return $this->autoBalance() <= 0 ? 'Paid' : 'Balance';
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
