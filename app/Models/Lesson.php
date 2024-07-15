<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'schedule_id',
        'cabinet',
        'index'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }
}
