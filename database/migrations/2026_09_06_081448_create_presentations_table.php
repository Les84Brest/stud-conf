<?php
// database/migrations/2025_01_01_000009_create_presentations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_link')->nullable();
            $table->enum('status', [
                'draft', 
                'submitted', 
                'approved', 
                'rejected', 
                'presented'
            ])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('event_id');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('approved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};