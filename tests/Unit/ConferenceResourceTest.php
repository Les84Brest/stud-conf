<?php

namespace Tests\Unit;

use App\Filament\Resources\Conferences\ConferenceResource;
use Filament\Schemas\Schema;
use PHPUnit\Framework\TestCase;

class ConferenceResourceTest extends TestCase
{
    public function test_form_uses_filament_schema_api(): void
    {
        $schema = Schema::make();

        $result = ConferenceResource::form($schema);

        $this->assertInstanceOf(Schema::class, $result);
    }
}
