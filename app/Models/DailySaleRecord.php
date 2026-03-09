<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySaleRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'daily_sales_records';

    protected $fillable = [
        'date', 'assistant_id',
        'tickets_issued_qty', 'unit_price', 'value',
        'denom_20', 'denom_50', 'denom_100', 'denom_500', 'denom_1000', 'denom_5000',
        'cash',
        'nlb_winning', 'dlb_winning', 'total_winning',
        'cw', 'balance',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'unit_price'         => 'decimal:2',
            'value'              => 'decimal:2',
            'cash'               => 'decimal:2',
            'nlb_winning'        => 'decimal:2',
            'dlb_winning'        => 'decimal:2',
            'total_winning'      => 'decimal:2',
            'cw'                 => 'decimal:2',
            'balance'            => 'decimal:2',
        ];
    }

    /** Recalculate and set all derived fields from raw inputs. */
    public function compute(): void
    {
        $this->value         = $this->tickets_issued_qty * $this->unit_price;
        $this->cash          = ($this->denom_20   * 20)
                             + ($this->denom_50   * 50)
                             + ($this->denom_100  * 100)
                             + ($this->denom_500  * 500)
                             + ($this->denom_1000 * 1000)
                             + ($this->denom_5000 * 5000);
        $this->total_winning = $this->nlb_winning + $this->dlb_winning;
        $this->cw            = $this->cash + $this->total_winning;
        $this->balance       = $this->value - $this->cw;
    }

    /** positive balance = assistant owes (Credit/Outstanding) */
    public function statusLabel(): string
    {
        if ($this->balance > 0) return 'Outstanding';
        if ($this->balance < 0) return 'Overpaid';
        return 'Balanced';
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }
}
