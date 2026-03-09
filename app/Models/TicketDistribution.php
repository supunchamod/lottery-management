<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'assistant_id',
        'sub_seller_id',
        'lottery_id',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'qty'  => 'integer',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function subSeller(): BelongsTo
    {
        return $this->belongsTo(SubSeller::class, 'sub_seller_id');
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }
}
