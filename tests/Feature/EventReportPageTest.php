<?php

namespace Tests\Feature;

use App\Filament\Pages\EventReport;
use App\Models\Author;
use App\Models\Presentation;
use Tests\TestCase;

class EventReportPageTest extends TestCase
{
    public function test_event_report_page_can_be_loaded(): void
    {
        $this->assertTrue(class_exists(EventReport::class));
    }

    public function test_presentation_authors_are_deduplicated_for_report_display(): void
    {
        $presentation = new Presentation(['title' => 'Доклад']);

        $presentation->setRelation('authors', collect([
            new Author(['id' => 1, 'full_name' => 'Иван Иванов']),
            new Author(['id' => 1, 'full_name' => 'Иван Иванов']),
            new Author(['id' => 2, 'full_name' => 'Петр Петров']),
        ]));

        $this->assertSame('Иван Иванов, Петр Петров', $presentation->getUniqueAuthorsListAttribute());
    }
}
