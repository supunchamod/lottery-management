<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleLog extends Model
{
    protected $fillable = [
        'session_date',
        'total_bundles',
        'total_tickets',
        'scanned_barcodes',
        'notes',
        'saved_by',
    ];

    protected $casts = [
        'session_date'     => 'date',
        'scanned_barcodes' => 'array',
    ];

    public function savedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
