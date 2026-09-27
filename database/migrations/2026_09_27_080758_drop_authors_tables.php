<?php
// database/migrations/xxxx_drop_authors_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('presentation_author');
        Schema::dropIfExists('authors');
    }

    public function down(): void
    {
        // Восстанавливать не будем — данные уже перенесены
    }
};