<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winning extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'board_type',
        'cat_40',
        'cat_80',
        'cat_100',
        'cat_500',
        'cat_1000',
        'total_val',
    ];

    protected function casts(): array
    {
        return [
            'date'      => 'date',
            'total_val' => 'decimal:2',
        ];
    }

    /**
     * Compute the total payout from individual category counts.
     * Call this before saving when total_val is not provided manually.
     */
    public function computeTotal(): void
    {
        $this->total_val =
            ($this->cat_40   * 40)   +
            ($this->cat_80   * 80)   +
            ($this->cat_100  * 100)  +
            ($this->cat_500  * 500)  +
            ($this->cat_1000 * 1000);
    }
}
