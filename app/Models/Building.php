<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    // Указываем, что primary key — это поле name
    protected $primaryKey = 'name';

    public $incrementing = false;  // Отключаем автоинкремент

    protected $keyType = 'string';  // Устанавливаем тип ключа как строку

    protected $fillable = ['name', 'location'];
}
