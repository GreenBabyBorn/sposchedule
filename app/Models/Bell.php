<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bell extends Model
{
    protected $fillable = [
        'type',
        'week_day',
        'date',
        'building',
        'name_preset',
        'is_preset',
        'published',
    ];

    public const TYPE_MAIN = 'main';

    public const TYPE_CHANGES = 'changes';

    protected $casts = [
        'date' => 'date',
    ];

    // Связь с BellsPeriod
    public function periods(): HasMany
    {
        return $this->hasMany(BellsPeriod::class, 'bells_id')->orderBy('index', 'asc');
    }
}
