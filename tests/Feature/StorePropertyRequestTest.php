<?php

namespace Tests\Feature;

use App\Enums\PropertyStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePropertyRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_property_for_an_office(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $office = Office::factory()->create();
        $owner = Owner::factory()->create();
        $governorate = Governorate::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/properties', [
                'office_id' => $office->id,
                'owner_id' => $owner->id,
                'governorate_id' => $governorate->id,
                'title' => 'Apartment in Damascus',
                'description' => 'A test property description.',
                'property_type' => 'apartment',
                'listing_type' => 'sale',
                'price' => 50000,
                'currency' => 'USD',
                'area' => 120,
                'address' => 'Damascus, Syria',
                'contact_phone' => '0991234567',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Apartment in Damascus')
            ->assertJsonPath('data.status', 'pending_approval')
            ->assertJsonPath('data.office_id', $office->id);

        $this->assertDatabaseHas('properties', [
            'title' => 'Apartment in Damascus',
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'status' => 'pending_approval',
            'approved_at' => null,
        ]);
    }

    public function test_office_can_create_property_only_for_its_own_office(): void
    {
        $office = Office::factory()->create();
        $anotherOffice = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $owner = Owner::factory()->create();
        $governorate = Governorate::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/properties', [
                'office_id' => $anotherOffice->id,
                'owner_id' => $owner->id,
                'governorate_id' => $governorate->id,
                'title' => 'Office Test Property',
                'description' => 'A test property description.',
                'property_type' => 'apartment',
                'listing_type' => 'sale',
                'price' => 50000,
                'currency' => 'USD',
                'area' => 120,
                'address' => 'Latakia, Syria',
                'contact_phone' => '0991234567',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id']);

        $this->assertDatabaseMissing('properties', [
            'title' => 'Office Test Property',
        ]);
    }

    public function test_regular_user_cannot_create_property(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/properties', []);

        $response->assertForbidden();

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_property_creation_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/properties', []);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_property_creation_rejects_invalid_price_and_area(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
        'office_id' => null,
    ]);

    $office = Office::factory()->create();
    $owner = Owner::factory()->create();
    $governorate = Governorate::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/properties', [
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'Invalid Test Property',
            'description' => 'Testing invalid property data.',
            'property_type' => 'apartment',
            'listing_type' => 'sale',
            'price' => -100,
            'currency' => 'USD',
            'area' => 0,
            'address' => 'Damascus, Syria',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['price', 'area']);

    $this->assertDatabaseMissing('properties', [
        'title' => 'Invalid Test Property',
    ]);
}
}
