<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesAssistant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'current_balance',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(LotteryStock::class, 'agent_id');
    }

    public function dailySales(): HasMany
    {
        return $this->hasMany(DailySale::class, 'assistant_id');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'assistant_id');
    }

    public function subSellers(): HasMany
    {
        return $this->hasMany(SubSeller::class, 'assistant_id');
    }

    public function ticketDistributions(): HasMany
    {
        return $this->hasMany(TicketDistribution::class, 'assistant_id');
    }
}
