<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Админ',
            'email' => 'admin@mail.ru',
            'password' => Hash::make('123123'), // Шифруем пароль
        ]);
    }
}
