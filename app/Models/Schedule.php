<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'semester_id',
        'date',
        'type',
        // 'week_type',
        'week_day',
        'view_mode',
        'message',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}