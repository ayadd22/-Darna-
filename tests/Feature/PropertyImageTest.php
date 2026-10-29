<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\Image;
use App\Models\Office;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_admin_can_upload_property_image(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create('property.jpg',100,'image/jpeg'),
                'alt_text' => 'Living room',
                'sort_order' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('data.alt_text', 'Living room')
            ->assertJsonPath('data.sort_order', 1);

        $this->assertDatabaseHas('images', [
            'imageable_id' => $property->id,
            'imageable_type' => Property::class,
            'alt_text' => 'Living room',
            'sort_order' => 1,
        ]);

        $image = Image::where('imageable_id', $property->id)->first();

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_office_can_upload_image_to_its_own_property(): void
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
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create('property.jpg',100,'image/jpeg'),
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas('images', [
            'imageable_id' => $property->id,
            'imageable_type' => Property::class,
        ]);
    }

    public function test_office_cannot_upload_image_to_another_office_property(): void
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
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create('property.jpg',100,'image/jpeg'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('images', 0);
    }

    public function test_regular_user_cannot_upload_property_image(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create('property.jpg',100,'image/jpeg'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('images', 0);
    }

    public function test_guest_cannot_upload_property_image(): void
    {
        $property = Property::factory()->create();

        $this->postJson("/api/v1/properties/{$property->id}/images", [
            'image' => UploadedFile::fake()->create('property.jpg',100,'image/jpeg'),
        ])->assertUnauthorized();
    }

    public function test_upload_rejects_invalid_file(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create(
                    'document.pdf',
                    100,
                    'application/pdf'
                ),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('images', 0);
    }

    public function test_upload_rejects_image_larger_than_five_mb(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/properties/{$property->id}/images", [
                'image' => UploadedFile::fake()->create(
    'large.jpg',
    5121,
    'image/jpeg'
),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('images', 0);
    }

    public function test_admin_can_delete_property_image(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $path = "properties/{$property->id}/test-image.jpg";
        Storage::disk('public')->put($path, 'fake image content');

        $image = $property->images()->create([
            'path' => $path,
            'alt_text' => 'Test image',
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('message', 'تم حذف الصورة بنجاح.');

        $this->assertDatabaseMissing('images', [
            'id' => $image->id,
        ]);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_office_can_delete_image_from_its_own_property(): void
    {
        $office = Office::factory()->create();

        $user = User::factory()->create([
            'role' => UserRoleEnum::OFFICE,
            'office_id' => $office->id,
        ]);

        $property = Property::factory()->create([
            'office_id' => $office->id,
        ]);

        $path = "properties/{$property->id}/test-image.jpg";
        Storage::disk('public')->put($path, 'fake image content');

        $image = $property->images()->create([
            'path' => $path,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertOk();

        $this->assertDatabaseMissing('images', [
            'id' => $image->id,
        ]);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_office_cannot_delete_image_from_another_office_property(): void
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

        $path = "properties/{$property->id}/test-image.jpg";
        Storage::disk('public')->put($path, 'fake image content');

        $image = $property->images()->create([
            'path' => $path,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('images', [
            'id' => $image->id,
        ]);

        Storage::disk('public')->assertExists($path);
    }

    public function test_regular_user_cannot_delete_property_image(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $path = "properties/{$property->id}/test-image.jpg";
        Storage::disk('public')->put($path, 'fake image content');

        $image = $property->images()->create([
            'path' => $path,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('images', [
            'id' => $image->id,
        ]);

        Storage::disk('public')->assertExists($path);
    }

    public function test_guest_cannot_delete_property_image(): void
    {
        $property = Property::factory()->create();

        $image = $property->images()->create([
            'path' => "properties/{$property->id}/test-image.jpg",
        ]);

        $this->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertUnauthorized();
    }

    public function test_image_from_another_property_cannot_be_deleted_using_wrong_property_id(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();
        $anotherProperty = Property::factory()->create();

        $image = $anotherProperty->images()->create([
            'path' => "properties/{$anotherProperty->id}/test-image.jpg",
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}/images/{$image->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('images', [
            'id' => $image->id,
        ]);
    }

    public function test_property_show_response_includes_its_images(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'office_id' => null,
        ]);

        $property = Property::factory()->create();

        $image = $property->images()->create([
            'path' => "properties/{$property->id}/test-image.jpg",
            'alt_text' => 'Property front view',
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.images.0.id', $image->id)
            ->assertJsonPath('data.images.0.alt_text', 'Property front view');
    }
}