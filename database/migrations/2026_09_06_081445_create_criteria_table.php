<?php
// database/migrations/2025_01_01_000006_create_criteria_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Меняем имя таблицы на 'criterias' (множественное число)
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_group_id')
                  ->nullable()
                  ->constrained('criteria_groups') // указываем имя таблицы явно
                  ->onDelete('set null');
            $table->string('name');
            $table->string('key')->unique();
            $table->integer('max_value')->default(3);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterias');
    }
};