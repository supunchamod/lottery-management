<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'date',
        'title',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
