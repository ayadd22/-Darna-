<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_any_property(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
        ]);

        $property = Property::factory()->create();

        $this->assertTrue(
            $admin->can('update', $property)
        );
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
        ]);

        $this->assertTrue(
            $user->can('update', $property)
        );
    }

    public function test_office_cannot_update_another_office_property(): void
    {
        $officeA = Office::factory()->create();
        $officeB = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $officeA->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $officeB->id,
        ]);

        $this->assertFalse(
            $user->can('update', $property)
        );
    }

    public function test_regular_user_cannot_update_a_property(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
        ]);

        $property = Property::factory()->create();

        $this->assertFalse(
            $user->can('update', $property)
        );
    }



    
public function test_admin_can_view_any_properties(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
    ]);

    $this->assertTrue($admin->can('viewAny', Property::class));
}

public function test_regular_user_cannot_view_any_properties_through_policy(): void
{
    $user = User::factory()->create([
        'role' => UserRoleEnum::USER,
    ]);

    $this->assertFalse($user->can('viewAny', Property::class));
}

public function test_office_can_view_its_own_property(): void
{
    $office = Office::factory()->create();

    $user = User::factory()->create([
        'role' => UserRoleEnum::OFFICE,
        'office_id' => $office->id,
    ]);

    $property = Property::factory()->create([
        'office_id' => $office->id,
    ]);

    $this->assertTrue($user->can('view', $property));
}

public function test_office_cannot_view_another_office_property_through_policy(): void
{
    $officeA = Office::factory()->create();
    $officeB = Office::factory()->create();

    $user = User::factory()->create([
        'role' => UserRoleEnum::OFFICE,
        'office_id' => $officeA->id,
    ]);

    $property = Property::factory()->create([
        'office_id' => $officeB->id,
    ]);

    $this->assertFalse($user->can('view', $property));
}

public function test_admin_can_create_properties(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
    ]);

    $this->assertTrue($admin->can('create', Property::class));
}

public function test_office_can_create_properties(): void
{
    $office = Office::factory()->create();

    $user = User::factory()->create([
        'role' => UserRoleEnum::OFFICE,
        'office_id' => $office->id,
    ]);

    $this->assertTrue($user->can('create', Property::class));
}

public function test_regular_user_cannot_create_properties(): void
{
    $user = User::factory()->create([
        'role' => UserRoleEnum::USER,
    ]);

    $this->assertFalse($user->can('create', Property::class));
}

public function test_admin_can_delete_any_property(): void
{
    $admin = User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
    ]);

    $property = Property::factory()->create();

    $this->assertTrue($admin->can('delete', $property));
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

    $this->assertTrue($user->can('delete', $property));
}

public function test_office_cannot_delete_another_office_property(): void
{
    $officeA = Office::factory()->create();
    $officeB = Office::factory()->create();

    $user = User::factory()->create([
        'role' => UserRoleEnum::OFFICE,
        'office_id' => $officeA->id,
    ]);

    $property = Property::factory()->create([
        'office_id' => $officeB->id,
    ]);

    $this->assertFalse($user->can('delete', $property));
}

public function test_regular_user_cannot_delete_properties(): void
{
    $user = User::factory()->create([
        'role' => UserRoleEnum::USER,
    ]);

    $property = Property::factory()->create();

    $this->assertFalse($user->can('delete', $property));
}




    public function test_admin_can_approve_a_property(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
        ]);

        $this->assertTrue(
            $admin->can('approve', Property::class)
        );
    }

    public function test_office_cannot_approve_a_property(): void
    {
        $office = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $this->assertFalse(
            $user->can('approve', Property::class)
        );
    }

    public function test_regular_user_cannot_approve_a_property(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
        ]);

        $this->assertFalse(
            $user->can('approve', Property::class)
        );
    }


}
