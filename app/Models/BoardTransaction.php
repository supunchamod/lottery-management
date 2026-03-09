<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class BoardTransaction extends Model
{
    use HasFactory;

    // ── Denomination maps (reused in form and controller) ─────────────────────

    public const NLB_TIERS = [
        'nlb_40'    => 40,
        'nlb_80'    => 80,
        'nlb_120'   => 120,
        'nlb_160'   => 160,
        'nlb_200'   => 200,
        'nlb_240'   => 240,
        'nlb_500'   => 500,
        'nlb_1000'  => 1000,
        'nlb_1080'  => 1080,
        'nlb_2000'  => 2000,
        'nlb_4000'  => 4000,
        'nlb_5000'  => 5000,
        'nlb_6000'  => 6000,
        'nlb_15000' => 15000,
    ];

    public const DLB_TIERS = [
        'dlb_40'   => 40,
        'dlb_80'   => 80,
        'dlb_120'  => 120,
        'dlb_200'  => 200,
        'dlb_240'  => 240,
        'dlb_280'  => 280,
        'dlb_400'  => 400,
        'dlb_500'  => 500,
        'dlb_1000' => 1000,
        'dlb_2000' => 2000,
        'dlb_4000' => 4000,
    ];

    public const CASH_DENOMS = [
        'cash_10'   => 10,
        'cash_20'   => 20,
        'cash_50'   => 50,
        'cash_100'  => 100,
        'cash_500'  => 500,
        'cash_1000' => 1000,
        'cash_2000' => 2000,
        'cash_5000' => 5000,
    ];

    protected $fillable = [
        'board_settlement_id',
        'date', 'date_02', 'description',
        'ticket_qty', 'ticket_value',
        'winning_amount', 'nlb_winning', 'dlb_winning',
        'cash_amount', 'bank_deposits',
        'nlb_tiers', 'dlb_tiers', 'cash_denoms',
        'credit_amount',
        'cr_amount', 'balance',
        'notes',
    ];

    // ── Relationship ───────────────────────────────────────────────────────────

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BoardSettlement::class, 'board_settlement_id');
    }

    protected function casts(): array
    {
        return [
            'date'           => 'date',
            'date_02'        => 'date',
            'ticket_value'   => 'decimal:2',
            'winning_amount' => 'decimal:2',
            'nlb_winning'    => 'decimal:2',
            'dlb_winning'    => 'decimal:2',
            'cash_amount'    => 'decimal:2',
            'bank_deposits'  => 'decimal:2',
            'credit_amount'  => 'decimal:2',
            'cr_amount'      => 'decimal:2',
            'balance'        => 'decimal:2',
            'nlb_tiers'      => 'array',
            'dlb_tiers'      => 'array',
            'cash_denoms'    => 'array',
        ];
    }

    // ── Business logic ────────────────────────────────────────────────────────

    /**
     * Calculate and set cr_amount based on description + values.
     * Call before save().
     */
    public function computeCrAmount(): void
    {
        $this->cr_amount = match ($this->description) {
            'get_tickets' => (float) $this->ticket_value,
            'paid_bill'   => -(
                (float) $this->winning_amount +
                (float) $this->cash_amount +
                (float) $this->bank_deposits
            ),
            'credit'      => (float) $this->credit_amount,
            default       => 0,
        };
    }

    /**
     * Rebuild running balances for ALL rows in chronological order.
     * Must be called after any insert, update, or delete that affects amounts.
     * Uses a single ordered query then batches updates for performance.
     */
    public static function recalculateBalances(): void
    {
        $running = 0;

        self::orderBy('date')
            ->orderBy('id')
            ->each(function (self $tx) use (&$running) {
                $running += (float) $tx->cr_amount;
                if ((float) $tx->balance !== $running) {
                    $tx->balance = $running;
                    $tx->saveQuietly();
                }
            });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeDateBetween($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('date', '>=', $from);
        if ($to)   $query->whereDate('date', '<=', $to);
        return $query;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDescriptionLabelAttribute(): string
    {
        return match ($this->description) {
            'get_tickets' => 'Get Tickets',
            'paid_bill'   => 'Paid Bill',
            'credit'      => 'Credit',
            default       => $this->description,
        };
    }

    /**
     * Compute total winning from the stored nlb_tiers + dlb_tiers JSON.
     * Useful for display when you need the per-denomination breakdown.
     */
    public function computeWinningFromTiers(): float
    {
        $total = 0;
        foreach ((array) $this->nlb_tiers as $col => $qty) {
            $total += ($qty * (self::NLB_TIERS[$col] ?? 0));
        }
        foreach ((array) $this->dlb_tiers as $col => $qty) {
            $total += ($qty * (self::DLB_TIERS[$col] ?? 0));
        }
        return $total;
    }
}
