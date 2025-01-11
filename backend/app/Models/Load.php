<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;
    protected $fillable = ['semester_id','teacher_id', 'subject_id', 'group_id', 'hours'];


    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
