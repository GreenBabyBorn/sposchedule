<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Facades\HistoryLogger;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Выполняем автоматический вход после регистрации
        Auth::login($user);

        // Создаем токен для нового пользователя
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'message' => 'Успешная регистрация',
            'token' => $token // Возвращаем токен
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->remember ? true : false)) {
            return response()->json(['message' => 'Неверный логин или пароль'], 401);
        }

        $user = $request->user();
        // Создание токена для пользователя
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'message' => 'Добро пожаловать!',
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user = $request->user();

        if($user) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Выход успешный']);
    }

    public function updateProfile(Request $request)
    {
        // Получаем текущего пользователя
        $user = $request->user();

        // Валидация данных
        $request->validate([
            'name' => 'sometimes|string|max:255', // Поле может быть передано, но не обязательно
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id, // Уникальный email, игнорируем текущего пользователя
            'password' => 'sometimes|string|min:6|confirmed', // Обновление пароля, если передан
        ]);

        // Обновление имени, если передано
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // Обновление email, если передано
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Обновление пароля, если передано
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
            HistoryLogger::logAction('Пароль был изменен');
        }
        HistoryLogger::logAction('Профиль обновлен');
        // Сохраняем изменения
        $user->save();

        return response()->json([
            'message' => 'Профиль успешно обновлен',
            'user' => $user
        ]);
    }
}
