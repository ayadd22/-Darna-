<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletePropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_any_property(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertOk()
            ->assertJson([
                'message' => 'تم حذف العقار بنجاح.',
            ]);

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_office_can_delete_its_own_property(): void
    {
        $office = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $office->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertOk();

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_office_cannot_delete_another_office_property(): void
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
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_regular_user_cannot_delete_property(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_guest_cannot_delete_property(): void
    {
        $property = Property::factory()->create();

        $this->deleteJson("/api/v1/properties/{$property->id}")
            ->assertUnauthorized();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_deleting_nonexistent_property_returns_not_found(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
        'office_id' => null,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/v1/properties/999999')
        ->assertNotFound();
}
}
