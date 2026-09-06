<?php
// database/migrations/2025_01_01_000012_create_assessment_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('assessment_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_logs');
    }
};