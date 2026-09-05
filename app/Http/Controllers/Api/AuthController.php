<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя (только для администратора)
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'sometimes|in:admin,expert,observer',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
            'role' => $validated['role'] ?? 'expert',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Пользователь создан успешно',
            'user' => $user,
            'temporary_password' => 'password123',
        ], 201);
    }

    /**
     * Вход в систему
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ваш аккаунт заблокирован. Обратитесь к администратору.'],
            ]);
        }

        // Обновляем время последнего входа
        $user->updateLastLogin();

        // Создаем токен
        $token = $user->createToken('auth_token', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Вход выполнен успешно',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Выход выполнен успешно'
        ]);
    }

    /**
     * Получение информации о текущем пользователе
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Проверка токена
     */
    public function check(Request $request): JsonResponse
    {
        return response()->json([
            'authenticated' => $request->user() !== null,
            'user' => $request->user()
        ]);
    }

    /**
     * Смена пароля
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль неверен.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'Пароль успешно изменен'
        ]);
    }

    /**
     * Сброс пароля пользователя (только для администратора)
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $newPassword = 'password123';
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return response()->json([
            'message' => 'Пароль сброшен успешно',
            'new_password' => $newPassword
        ]);
    }
}