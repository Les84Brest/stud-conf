<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserImportService
{
    /**
     * Импортировать экспертов.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created: int, skipped: int, errors: array<int, array{row: int, message: string}>, credentials: array<int, array{name: string, email: string, password: string}>}
     */
    public function import(array $rows): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];
        $credentials = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $user = $this->importRow($row);
                    $created++;

                    $credentials[] = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $row['_plain_password'] ?? '',
                    ];
                } catch (\Throwable $e) {
                    $skipped++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'credentials' => $credentials,
        ];
    }

    /**
     * Импортировать одну строку.
     *
     * @throws \RuntimeException
     */
    protected function importRow(array $row): User
    {
        $row = $this->normalizeKeys($row);

        // Извлекаем поля
        $name = trim((string) ($row['фио'] ?? $row['name'] ?? ''));
        $email = trim((string) ($row['email'] ?? ''));
        $password = (string) ($row['пароль'] ?? $row['password'] ?? '');

        // Быстрая валидация
        if ($name === '') {
            throw new \RuntimeException('Не указано ФИО');
        }
        if ($email === '') {
            throw new \RuntimeException('Не указан email');
        }
        if ($password === '') {
            throw new \RuntimeException('Не указан пароль');
        }
        if (mb_strlen($password) < 8) {
            throw new \RuntimeException('Пароль должен быть не менее 8 символов');
        }

        // Валидация через Laravel
        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
            ],
            [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email'),
                ],
            ]
        );

        if ($validator->fails()) {
            throw new \RuntimeException(
                (string) collect($validator->errors()->all())->first()
            );
        }

        // Создаём эксперта
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_EXPERT,
            'is_active' => true,
        ]);

        // Сохраняем plain-пароль для отчёта
        $user->setAttribute('_plain_password', $password);

        return $user;
    }

    /**
     * Нормализовать ключи строки.
     */
    protected function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::of((string) $key)
                ->trim()
                ->lower()
                ->replaceMatches('/\s+/', '_')
                ->toString();
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }
}