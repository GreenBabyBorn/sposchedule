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
        'building'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building', 'name');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($group) {
            $group->name = $group->specialization . '-' . $group->course . $group->index;
        });
    }
}
