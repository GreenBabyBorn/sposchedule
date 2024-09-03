<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bell extends Model
{
    protected $fillable = [
        'type',
        'variant',
        'week_day',
        'date',
    ];

    // Определение вариантов расписания
    public const VARIANT_NORMAL = 'normal';
    public const VARIANT_REDUCED = 'reduced';

    public const TYPE_MAIN = 'main';
    public const TYPE_CHANGES = 'changes';

    protected $casts = [
        'date' => 'date',
    ];

    // Связь с BellsPeriod
    public function periods(): HasMany
    {
        return $this->hasMany(BellsPeriod::class, 'bells_id');
    }
}
