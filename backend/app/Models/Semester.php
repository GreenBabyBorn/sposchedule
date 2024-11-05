<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'years',
        'index',
        'start',
        'end',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
