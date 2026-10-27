<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_any_property(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create([
            'title' => 'Old Title',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Updated Title',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_office_can_update_its_own_property(): void
    {
        $office = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $office->id,
            'title' => 'Old Title',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Office Updated Title',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Office Updated Title');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Office Updated Title',
        ]);
    }

    public function test_office_cannot_update_another_office_property(): void
    {
        $office = Office::factory()->create();
        $anotherOffice = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $anotherOffice->id,
            'title' => 'Original Title',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Unauthorized Change',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_regular_user_cannot_update_property(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $property = Property::factory()->create([
            'title' => 'Original Title',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Unauthorized Change',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_guest_cannot_update_property(): void
    {
        $property = Property::factory()->create();

        $this->putJson("/api/v1/properties/{$property->id}", [
            'title' => 'Unauthorized Change',
        ])->assertUnauthorized();
    }

    public function test_update_rejects_invalid_property_data(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create([
            'price' => 10000,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'price' => -100,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'price' => 10000,
        ]);
    }

    public function test_update_cannot_change_property_office(): void
    {
        $office = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $office->id,
        ]);

        $anotherOffice = Office::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'office_id' => $anotherOffice->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'office_id' => $office->id,
        ]);
    }
    public function test_updating_nonexistent_property_returns_not_found(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
        'office_id' => null,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/properties/999999', [
            'title' => 'Updated Title',
        ])
        ->assertNotFound();
}
}
