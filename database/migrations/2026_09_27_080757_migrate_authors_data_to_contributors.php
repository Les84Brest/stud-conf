<?php
// database/migrations/xxxx_migrate_authors_data_to_contributors.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Переносим всех авторов докладов в JSON
        $presentations = DB::table('presentations')->get();

        foreach ($presentations as $presentation) {
            $authors = DB::table('presentation_author')
                ->join('authors', 'authors.id', '=', 'presentation_author.author_id')
                ->where('presentation_author.presentation_id', $presentation->id)
                ->orderBy('presentation_author.order')
                ->select([
                    'authors.full_name',
                    'authors.email',
                    'authors.university',
                    'authors.faculty',
                    'authors.group_number',
                    'authors.phone',
                    'authors.degree',
                    'authors.position',
                    'presentation_author.is_presenter',
                    'presentation_author.is_corresponding',
                    'presentation_author.order',
                ])
                ->get();

            $authorsArray = $authors->map(function ($author) {
                return [
                    'full_name' => $author->full_name,
                    'email' => $author->email,
                    'university' => $author->university,
                    'faculty' => $author->faculty,
                    'group_number' => $author->group_number,
                    'phone' => $author->phone,
                    'degree' => $author->degree,
                    'position' => $author->position,
                    'is_presenter' => (bool) $author->is_presenter,
                    'is_corresponding' => (bool) $author->is_corresponding,
                    'order' => (int) $author->order,
                ];
            })->values()->all();

            DB::table('presentations')
                ->where('id', $presentation->id)
                ->update([
                    'contributors' => json_encode([
                        'authors' => $authorsArray,
                        'supervisors' => [], // пока пусто, заполним вручную
                    ], JSON_UNESCAPED_UNICODE),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('presentations')->update(['contributors' => null]);
    }
};