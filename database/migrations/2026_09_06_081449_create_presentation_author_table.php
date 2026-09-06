<?php
// database/migrations/2025_01_01_000010_create_presentation_author_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_author', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('author_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->boolean('is_presenter')->default(false);
            $table->boolean('is_corresponding')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['presentation_id', 'author_id']);
            $table->index('presentation_id');
            $table->index('author_id');
            $table->index('is_presenter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_author');
    }
};