<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellsPeriod extends Model
{
    protected $fillable = [
        'bells_id',
        'index',
        'has_break',
        'period_from',
        'period_to',
        'period_from_after',
        'period_to_after',
    ];

    // Связь с Bells
    public function bells(): BelongsTo
    {
        return $this->belongsTo(Bell::class, 'bells_id');
    }
}
