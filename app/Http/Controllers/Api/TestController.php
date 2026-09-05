<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Только для администраторов
     */
    public function adminOnly()
    {
        return response()->json([
            'message' => 'Привет, администратор!',
            'user' => auth()->user()
        ]);
    }

    /**
     * Только для экспертов
     */
    public function expertOnly()
    {
        return response()->json([
            'message' => 'Привет, эксперт!',
            'user' => auth()->user()
        ]);
    }

    /**
     * Для всех авторизованных пользователей
     */
    public function allRoles()
    {
        return response()->json([
            'message' => 'Доступ для всех авторизованных пользователей',
            'user' => auth()->user()
        ]);
    }
}