<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTicketNote extends Model
{
    protected $fillable = [
        'date',
        'assistant_id',
        'is_no_sales',
        'is_handed_over',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date'           => 'date',
            'is_no_sales'    => 'boolean',
            'is_handed_over' => 'boolean',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }
}
