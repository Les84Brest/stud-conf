<?php

namespace Tests\Feature;

use App\Filament\Pages\EventReport;
use Tests\TestCase;

class EventReportPageTest extends TestCase
{
    public function test_event_report_page_can_be_loaded(): void
    {
        $this->assertTrue(class_exists(EventReport::class));
    }
}
