<?php

namespace Tests\Feature;

use App\Enums\PropertyStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyApprovalTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function admin(): User
    {
        return User::factory()->create([
            'role'      => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);
    }

    private function pendingProperty(): Property
    {
        return Property::factory()->create([
            'status'      => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);
    }

    // =========================================================================
    // APPROVE
    // =========================================================================

    public function test_admin_can_approve_a_pending_property(): void
    {
        $admin    = $this->admin();
        $property = $this->pendingProperty();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', PropertyStatusEnum::AVAILABLE->value);

        $this->assertDatabaseHas('properties', [
            'id'     => $property->id,
            'status' => PropertyStatusEnum::AVAILABLE->value,
        ]);

        // approved_at must be set and must not remain null.
        $property->refresh();
        $this->assertNotNull($property->approved_at);
    }

    // =========================================================================
    // REJECT
    // =========================================================================

    public function test_admin_can_reject_a_pending_property(): void
    {
        $admin    = $this->admin();
        $property = $this->pendingProperty();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', PropertyStatusEnum::REJECTED->value);

        $this->assertDatabaseHas('properties', [
            'id'          => $property->id,
            'status'      => PropertyStatusEnum::REJECTED->value,
            'approved_at' => null,
        ]);
    }

    // =========================================================================
    // UNAUTHENTICATED — 401
    // =========================================================================

    public function test_unauthenticated_request_to_approve_returns_401(): void
    {
        $property = $this->pendingProperty();

        $this->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertUnauthorized();
    }

    public function test_unauthenticated_request_to_reject_returns_401(): void
    {
        $property = $this->pendingProperty();

        $this->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertUnauthorized();
    }

    // =========================================================================
    // NON-ADMIN — 403
    // =========================================================================

    public function test_office_user_cannot_approve_a_property(): void
    {
        $office = Office::factory()->create();
        $user   = User::factory()->create([
            'role'      => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);
        $property = $this->pendingProperty();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertForbidden();

        // Status must not have changed.
        $this->assertDatabaseHas('properties', [
            'id'     => $property->id,
            'status' => PropertyStatusEnum::PENDING_APPROVAL->value,
        ]);
    }

    public function test_office_user_cannot_reject_a_property(): void
    {
        $office = Office::factory()->create();
        $user   = User::factory()->create([
            'role'      => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);
        $property = $this->pendingProperty();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id'     => $property->id,
            'status' => PropertyStatusEnum::PENDING_APPROVAL->value,
        ]);
    }

    public function test_regular_user_cannot_approve_a_property(): void
    {
        $user     = User::factory()->create(['role' => UserRoleEnum::USER, 'office_id' => null]);
        $property = $this->pendingProperty();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertForbidden();
    }

    public function test_regular_user_cannot_reject_a_property(): void
    {
        $user     = User::factory()->create(['role' => UserRoleEnum::USER, 'office_id' => null]);
        $property = $this->pendingProperty();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertForbidden();
    }

    // =========================================================================
    // INVALID STATE TRANSITION — 422
    // =========================================================================

    public function test_approving_an_already_approved_property_returns_422(): void
    {
        $admin    = $this->admin();
        // Factory default: status = AVAILABLE, approved_at = now()
        $property = Property::factory()->create([
            'status'      => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'لا يمكن الموافقة على هذا العقار لأنه ليس في حالة انتظار الموافقة.'
            );

        // Status must be unchanged.
        $this->assertDatabaseHas('properties', [
            'id'     => $property->id,
            'status' => PropertyStatusEnum::AVAILABLE->value,
        ]);
    }

    public function test_rejecting_an_already_rejected_property_returns_422(): void
    {
        $admin    = $this->admin();
        $property = Property::factory()->create([
            'status'      => PropertyStatusEnum::REJECTED,
            'approved_at' => null,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'لا يمكن رفض هذا العقار لأنه ليس في حالة انتظار الموافقة.'
            );

        $this->assertDatabaseHas('properties', [
            'id'     => $property->id,
            'status' => PropertyStatusEnum::REJECTED->value,
        ]);
    }

    public function test_approving_a_reserved_property_returns_422(): void
    {
        $admin    = $this->admin();
        $property = Property::factory()->create([
            'status' => PropertyStatusEnum::RESERVED,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/approve")
            ->assertUnprocessable();
    }

    public function test_rejecting_an_available_property_returns_422(): void
    {
        $admin    = $this->admin();
        $property = Property::factory()->create([
            'status'      => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/properties/{$property->id}/reject")
            ->assertUnprocessable();
    }

    // =========================================================================
    // NON-EXISTENT PROPERTY — 404
    // =========================================================================

    public function test_approving_a_nonexistent_property_returns_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/properties/999999/approve')
            ->assertNotFound();
    }

    public function test_rejecting_a_nonexistent_property_returns_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/properties/999999/reject')
            ->assertNotFound();
    }
}
