<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Semester;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'course',
        'index',
        'specialization',
        'name',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class);
    }
}
