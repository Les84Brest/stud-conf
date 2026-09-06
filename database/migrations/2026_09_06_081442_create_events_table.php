<?php
// database/migrations/2025_01_01_000003_create_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->enum('type', ['section', 'olympiad', 'round_table', 'master_class'])
                  ->default('section');
            $table->text('description')->nullable();
            $table->integer('max_score')->default(19);
            $table->string('room')->nullable();
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['conference_id', 'slug']);
            $table->index('type');
            $table->index('is_active');
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};