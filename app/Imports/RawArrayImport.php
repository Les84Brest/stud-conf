<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class RawArrayImport implements ToArray
{
    public array $rows = [];

    /**
     * Получаем массив строк из файла.
     * Первая строка — заголовки, остальные — данные.
     */
    public function array(array $rows): void
    {
        $this->rows = $rows;
    }
}