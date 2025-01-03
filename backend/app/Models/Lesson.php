<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'schedule_id',
        'cabinet',
        'index',
        'building',
        'week_type',
        'message',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    protected static function booted()
    {
        // При любом изменении, вставке или удалении обновляем расписание
        static::created(function ($lesson) {
            $lesson->schedule->touch(); // обновляет поле updated_at у Schedule
        });

        static::updated(function ($lesson) {
            $lesson->schedule->touch();
        });

        static::deleted(function ($lesson) {
            $lesson->schedule->touch();
        });
    }
}
