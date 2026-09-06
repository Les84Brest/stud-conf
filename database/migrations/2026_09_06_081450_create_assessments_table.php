<?php
// database/migrations/2025_01_01_000011_create_assessments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('expert_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('event_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->json('criteria_values');
            $table->integer('total_score');
            $table->text('comment')->nullable();
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['presentation_id', 'expert_id', 'event_id'], 'unique_assessment');
            $table->index('expert_id');
            $table->index('presentation_id');
            $table->index('event_id');
            $table->index('total_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};