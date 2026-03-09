<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winning extends Model
{
    use HasFactory;

    /**
     * NLB denomination => face value map.
     */
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

    /**
     * DLB denomination => face value map.
     */
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

    protected $fillable = [
        'date',
        // NLB tiers
        'nlb_40', 'nlb_80', 'nlb_120', 'nlb_160', 'nlb_200', 'nlb_240',
        'nlb_500', 'nlb_1000', 'nlb_1080', 'nlb_2000', 'nlb_4000',
        'nlb_5000', 'nlb_6000', 'nlb_15000', 'nlb_total',
        // DLB tiers
        'dlb_40', 'dlb_80', 'dlb_120', 'dlb_200', 'dlb_240', 'dlb_280',
        'dlb_400', 'dlb_500', 'dlb_1000', 'dlb_2000', 'dlb_4000', 'dlb_total',
        // Grand total
        'total_val',
    ];

    protected function casts(): array
    {
        return [
            'date'      => 'date',
            'nlb_total' => 'decimal:2',
            'dlb_total' => 'decimal:2',
            'total_val' => 'decimal:2',
        ];
    }

    /**
     * Compute and populate nlb_total, dlb_total, and total_val
     * from the individual tier counts. Call before save() when
     * not relying on a DB-generated column.
     */
    public function computeTotals(): void
    {
        $nlb = 0;
        foreach (self::NLB_TIERS as $col => $faceValue) {
            $nlb += ($this->{$col} ?? 0) * $faceValue;
        }

        $dlb = 0;
        foreach (self::DLB_TIERS as $col => $faceValue) {
            $dlb += ($this->{$col} ?? 0) * $faceValue;
        }

        $this->nlb_total = $nlb;
        $this->dlb_total = $dlb;
        $this->total_val = $nlb + $dlb;
    }
}
