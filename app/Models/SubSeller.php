<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubSeller extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'assistant_id',
        'name',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(SalesAssistant::class, 'assistant_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(TicketDistribution::class, 'sub_seller_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
