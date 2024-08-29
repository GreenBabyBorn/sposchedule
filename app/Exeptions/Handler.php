<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    // Другие методы и свойства...

    /**
    * Convert an authentication exception into an unauthenticated response.
    *
    * @param \Illuminate\Http\Request $request
    * @param \Illuminate\Auth\AuthenticationException $exception
    * @return \Illuminate\Http\Response
    */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Проверяем, является ли запрос AJAX или это обычный HTTP-запрос
        if ($request->expectsJson()) {
            return response()->json([
            'message' => 'Пользователь не авторизован', // Ваше кастомное сообщение
            'error' => 'Unauthenticated'
            ], 401);
        }

        // Для обычных HTTP-запросов можно перенаправить на страницу логина или другую страницу
        return redirect()->guest(route('login'));
    }
}
