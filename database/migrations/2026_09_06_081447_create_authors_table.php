<?php
// database/migrations/2025_01_01_000008_create_authors_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('university')->nullable();
            $table->string('faculty')->nullable();
            $table->string('group_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('degree')->nullable(); // ученая степень
            $table->string('position')->nullable(); // должность
            $table->timestamps();
            
            $table->index('full_name');
            $table->index('email');
            $table->index('university');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};