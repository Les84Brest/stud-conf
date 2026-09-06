<?php
// database/migrations/2025_01_01_000007_create_event_criteria_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('criteria_id')
                  ->constrained('criterias') // Указываем имя таблицы явно
                  ->onDelete('cascade');
            $table->integer('max_value_override')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['event_id', 'criteria_id']);
            $table->index('event_id');
            $table->index('criteria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_criteria');
    }
};