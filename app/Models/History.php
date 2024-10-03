<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'details'];

    protected $casts = [
        'details' => 'array', // Чтобы json-пример был автоматически преобразован в массив
    ];

    // Связь с моделью User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
