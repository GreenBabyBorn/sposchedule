<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'course',
        'index',
        'specialization',
        'name',
    ];

    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'group_building', 'group_id', 'building_name');
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
            $group->name = $group->specialization.'-'.$group->course.$group->index;
        });
    }
}
