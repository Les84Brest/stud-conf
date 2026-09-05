<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Не авторизован'
            ], 401);
        }

        // Проверяем, имеет ли пользователь нужную роль
        $userRole = Auth::user()->role;
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуется роль: ' . implode(' или ', $roles),
                'your_role' => $userRole
            ], 403);
        }

        return $next($request);
    }
}