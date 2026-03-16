<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lottery extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'board',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(LotteryStock::class, 'lottery_id');
    }
}
