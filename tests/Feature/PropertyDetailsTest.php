<?php

namespace Tests\Feature;

use App\Enums\PropertyStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_available_approved_property(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        $this->getJson("/api/v1/properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $property->id)
            ->assertJsonPath('data.title', $property->title);
    }

    public function test_guest_cannot_view_pending_property(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        $this->getJson("/api/v1/properties/{$property->id}")
            ->assertNotFound();
    }

    public function test_office_cannot_view_another_office_property(): void
    {
        $office = Office::factory()->create();
        $anotherOffice = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $anotherOffice->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/properties/{$property->id}")
            ->assertNotFound();
    }

    public function test_admin_can_view_pending_property(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create([
            'status' => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $property->id);
    }

    public function test_nonexistent_property_returns_not_found(): void
    {
        $this->getJson('/api/v1/properties/999999')
            ->assertNotFound();
    }
}
