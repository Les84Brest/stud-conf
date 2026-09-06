<?php
// database/migrations/2025_01_01_000004_create_expert_event_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('event_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'event_id']);
            $table->index('event_id');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_event');
    }
};