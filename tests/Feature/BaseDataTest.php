<?php

namespace Tests\Feature;

use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_governorates_endpoint_returns_governorates(): void
    {
        $governorate = Governorate::factory()->create();

        $this->getJson('/api/v1/governorates')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ])
            ->assertJsonFragment([
                'id' => $governorate->id,
                'name' => $governorate->name,
            ]);
    }

    public function test_offices_endpoint_returns_offices(): void
    {
        $office = Office::factory()->create();

        $this->getJson('/api/v1/offices')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ])
            ->assertJsonFragment([
                'id' => $office->id,
                'name' => $office->name,
            ]);
    }

    public function test_owners_endpoint_returns_owners(): void
    {
        $owner = Owner::factory()->create();

        $this->getJson('/api/v1/owners')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ])
            ->assertJsonFragment([
                'id' => $owner->id,
                'name' => $owner->name,
            ]);
    }
}