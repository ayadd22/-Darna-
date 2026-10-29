<?php

namespace Tests\Feature;

use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
use App\Models\Governorate;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Create a publicly-visible (available + approved) property with optional overrides. */
    private function availableProperty(array $overrides = []): Property
    {
        return Property::factory()->create(array_merge([
            'status'      => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // governorate_id filter
    // -------------------------------------------------------------------------

    public function test_filter_by_governorate_returns_matching_properties(): void
    {
        $target     = Governorate::factory()->create();
        $other      = Governorate::factory()->create();

        $match    = $this->availableProperty(['governorate_id' => $target->id]);
        $noMatch  = $this->availableProperty(['governorate_id' => $other->id]);

        $this->getJson("/api/v1/properties?governorate_id={$target->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_filter_by_nonexistent_governorate_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?governorate_id=99999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['governorate_id']);
    }

    // -------------------------------------------------------------------------
    // listing_type filter
    // -------------------------------------------------------------------------

    public function test_filter_by_listing_type_sale_returns_only_sale_properties(): void
    {
        $sale = $this->availableProperty(['listing_type' => ListingTypeEnum::SALE]);
        $rent = $this->availableProperty(['listing_type' => ListingTypeEnum::RENT]);

        $this->getJson('/api/v1/properties?listing_type=sale')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $sale->id);
    }

    public function test_filter_by_listing_type_rent_returns_only_rent_properties(): void
    {
        $sale = $this->availableProperty(['listing_type' => ListingTypeEnum::SALE]);
        $rent = $this->availableProperty(['listing_type' => ListingTypeEnum::RENT]);

        $this->getJson('/api/v1/properties?listing_type=rent')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $rent->id);
    }

    public function test_invalid_listing_type_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?listing_type=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['listing_type']);
    }

    // -------------------------------------------------------------------------
    // property_type filter
    // -------------------------------------------------------------------------

    public function test_filter_by_property_type_returns_matching_properties(): void
    {
        $apartment = $this->availableProperty(['property_type' => PropertyTypeEnum::APARTMENT]);
        $shop      = $this->availableProperty(['property_type' => PropertyTypeEnum::SHOP]);

        $this->getJson('/api/v1/properties?property_type=apartment')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $apartment->id);
    }

    public function test_invalid_property_type_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?property_type=villa')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['property_type']);
    }

    // -------------------------------------------------------------------------
    // min_price / max_price filters
    // -------------------------------------------------------------------------

    public function test_filter_by_min_price_excludes_cheaper_properties(): void
    {
        $cheap     = $this->availableProperty(['price' => 5_000]);
        $expensive = $this->availableProperty(['price' => 50_000]);

        $this->getJson('/api/v1/properties?min_price=10000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expensive->id);
    }

    public function test_filter_by_max_price_excludes_more_expensive_properties(): void
    {
        $cheap     = $this->availableProperty(['price' => 5_000]);
        $expensive = $this->availableProperty(['price' => 50_000]);

        $this->getJson('/api/v1/properties?max_price=10000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $cheap->id);
    }

    public function test_min_price_greater_than_max_price_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?min_price=50000&max_price=10000')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['min_price']);
    }

    public function test_negative_min_price_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?min_price=-1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['min_price']);
    }

    public function test_negative_max_price_fails_validation(): void
    {
        $this->getJson('/api/v1/properties?max_price=-1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['max_price']);
    }

    // -------------------------------------------------------------------------
    // Combined filters
    // -------------------------------------------------------------------------

    public function test_multiple_filters_are_applied_together(): void
    {
        $governorate = Governorate::factory()->create();

        // Matches all three criteria
        $match = $this->availableProperty([
            'governorate_id' => $governorate->id,
            'listing_type'   => ListingTypeEnum::RENT,
            'price'          => 20_000,
        ]);

        // Wrong governorate
        $this->availableProperty([
            'listing_type' => ListingTypeEnum::RENT,
            'price'        => 20_000,
        ]);

        // Wrong listing type
        $this->availableProperty([
            'governorate_id' => $governorate->id,
            'listing_type'   => ListingTypeEnum::SALE,
            'price'          => 20_000,
        ]);

        // Price out of range
        $this->availableProperty([
            'governorate_id' => $governorate->id,
            'listing_type'   => ListingTypeEnum::RENT,
            'price'          => 100_000,
        ]);

        $this->getJson(
            "/api/v1/properties?governorate_id={$governorate->id}&listing_type=rent&max_price=50000"
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    // -------------------------------------------------------------------------
    // Filters do not override visibility rules
    // -------------------------------------------------------------------------

    public function test_filters_do_not_expose_pending_properties_to_guests(): void
    {
        $governorate = Governorate::factory()->create();

        // Pending property in the target governorate — must stay hidden for guests
        Property::factory()->create([
            'governorate_id' => $governorate->id,
            'status'         => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at'    => null,
        ]);

        // Available property in the same governorate
        $visible = $this->availableProperty(['governorate_id' => $governorate->id]);

        $this->getJson("/api/v1/properties?governorate_id={$governorate->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id);
    }

    // -------------------------------------------------------------------------
    // No filters — baseline unchanged
    // -------------------------------------------------------------------------

    public function test_no_filters_returns_all_available_approved_properties_for_guests(): void
    {
        $this->availableProperty();
        $this->availableProperty();

        Property::factory()->create([
            'status'      => PropertyStatusEnum::PENDING_APPROVAL,
            'approved_at' => null,
        ]);

        $this->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
