<?php
// database/migrations/2025_01_01_000001_add_role_and_active_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'expert', 'observer'])
                  ->default('expert')
                  ->after('password');
                  
            $table->boolean('is_active')
                  ->default(true)
                  ->after('role');
                  
            $table->timestamp('last_login_at')
                  ->nullable()
                  ->after('remember_token');
            
            // Индексы для оптимизации
            $table->index('role');
            $table->index('is_active');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};