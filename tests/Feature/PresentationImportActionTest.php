<?php

namespace Tests\Feature;

use App\Filament\Actions\ImportPresentationsAction;
use Tests\TestCase;

class PresentationImportActionTest extends TestCase
{
    public function test_it_reads_excel_rows_with_a_valid_import_class(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'excel-import-');
        file_put_contents($file, "ФИО докладчика,Название доклада\nИванов Иван,Тестовый доклад\n");

        $rows = ImportPresentationsAction::readRowsFromFile($file);

        $this->assertNotEmpty($rows);
        $this->assertCount(2, $rows[0] ?? []);
        $this->assertSame('Иванов Иван', $rows[1]['ФИО докладчика'] ?? null);
        $this->assertSame('Тестовый доклад', $rows[1]['Название доклада'] ?? null);

        unlink($file);
    }
}
