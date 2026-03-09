<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardSettlement extends Model
{
    use HasFactory;

    // ── NLB denomination → face value ──────────────────────────────────────────
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

    // ── DLB denomination → face value ──────────────────────────────────────────
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

    // ── Cash denominations ──────────────────────────────────────────────────────
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
        'date',
        'total_tickets_received',
        'total_ticket_value',
        // NLB tiers
        'nlb_40', 'nlb_80', 'nlb_120', 'nlb_160', 'nlb_200', 'nlb_240',
        'nlb_500', 'nlb_1000', 'nlb_1080', 'nlb_2000', 'nlb_4000',
        'nlb_5000', 'nlb_6000', 'nlb_15000', 'nlb_total',
        // DLB tiers
        'dlb_40', 'dlb_80', 'dlb_120', 'dlb_200', 'dlb_240', 'dlb_280',
        'dlb_400', 'dlb_500', 'dlb_1000', 'dlb_2000', 'dlb_4000', 'dlb_total',
        // Grand winning
        'total_winning',
        // Cash counter
        'cash_10', 'cash_20', 'cash_50', 'cash_100',
        'cash_500', 'cash_1000', 'cash_2000', 'cash_5000', 'cash_total',
        // Payment summary
        'bank_deposits', 'total_paid', 'balance',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'                  => 'date',
            'total_ticket_value'    => 'decimal:2',
            'nlb_total'             => 'decimal:2',
            'dlb_total'             => 'decimal:2',
            'total_winning'         => 'decimal:2',
            'cash_total'            => 'decimal:2',
            'bank_deposits'         => 'decimal:2',
            'total_paid'            => 'decimal:2',
            'balance'               => 'decimal:2',
        ];
    }

    /**
     * Recompute all derived totals from tier/denomination quantities.
     * Call before save() when not using DB-generated columns.
     */
    public function computeTotals(): void
    {
        // NLB winning
        $nlb = 0;
        foreach (self::NLB_TIERS as $col => $denom) {
            $nlb += ((int) ($this->{$col} ?? 0)) * $denom;
        }
        $this->nlb_total = $nlb;

        // DLB winning
        $dlb = 0;
        foreach (self::DLB_TIERS as $col => $denom) {
            $dlb += ((int) ($this->{$col} ?? 0)) * $denom;
        }
        $this->dlb_total = $dlb;

        $this->total_winning = $nlb + $dlb;

        // Cash total
        $cash = 0;
        foreach (self::CASH_DENOMS as $col => $denom) {
            $cash += ((int) ($this->{$col} ?? 0)) * $denom;
        }
        $this->cash_total = $cash;

        // Payment summary
        $bankDep = (float) ($this->bank_deposits ?? 0);
        $this->total_paid = $this->total_winning + $cash + $bankDep;
        $this->balance    = ((float) ($this->total_ticket_value ?? 0)) - $this->total_paid;
    }
}
