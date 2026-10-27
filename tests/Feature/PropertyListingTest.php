<?php

namespace Tests\Feature;

use App\Enums\PropertyStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_only_available_approved_properties(): void
    {
        $available = Property::factory()->create([
            'status' => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        Property::factory()->create([
            'status' => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        Property::factory()->create([
            'status' => PropertyStatusEnum::AVAILABLE,
            'approved_at' => null,
        ]);

        $response = $this->getJson('/api/v1/properties');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $available->id);
    }

    public function test_regular_user_sees_only_available_approved_properties(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $available = Property::factory()->create([
            'status' => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        Property::factory()->create([
            'status' => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $available->id);
    }

    public function test_office_sees_only_its_own_properties(): void
    {
        $office = Office::factory()->create();
        $anotherOffice = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $ownProperty = Property::factory()->create([
            'office_id' => $office->id,
        ]);

        Property::factory()->create([
            'office_id' => $anotherOffice->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownProperty->id);
    }

    public function test_admin_sees_all_properties_including_pending_approval(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        Property::factory()->create([
            'status' => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        Property::factory()->create([
            'status' => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
