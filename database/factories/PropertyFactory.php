<?php

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'owner_id' => Owner::factory(),
            'governorate_id' => Governorate::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'property_type' => PropertyTypeEnum::APARTMENT,
            'listing_type' => ListingTypeEnum::SALE,
            'status' => PropertyStatusEnum::AVAILABLE,
            'price' => fake()->randomFloat(2, 10000, 1000000),
            'currency' => CurrencyEnum::USD,
            'area' => fake()->randomFloat(2, 40, 500),
            'address' => fake()->address(),
            'contact_phone' => fake()->numerify('09########'),
            'commission_rate' => null,
            'owner_notes' => null,
            'approved_at' => now(),
        ];
    }
}
