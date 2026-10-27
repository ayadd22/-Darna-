<?php

namespace Database\Seeders;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Seeder;
use RuntimeException;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $offices = Office::all();
        $owners = Owner::all();
        $governorates = Governorate::all();

        if (
            $offices->isEmpty()
            || $owners->isEmpty()
            || $governorates->isEmpty()
        ) {
            throw new RuntimeException(
                'Please seed offices, owners, and governorates before properties.'
            );
        }

        $properties = [
            [
                'title' => 'شقة للبيع في دمشق',
                'description' => 'شقة سكنية مناسبة للعائلات، تقع في منطقة حيوية.',
                'property_type' => PropertyTypeEnum::APARTMENT,
                'listing_type' => ListingTypeEnum::SALE,
                'status' => PropertyStatusEnum::AVAILABLE,
                'price' => 85000,
                'currency' => CurrencyEnum::USD,
                'area' => 120,
                'address' => 'دمشق، المزة',
                'contact_phone' => '0945000001',
            ],
            [
                'title' => 'محل تجاري للإيجار في حلب',
                'description' => 'محل تجاري بموقع مناسب للأعمال التجارية.',
                'property_type' => PropertyTypeEnum::SHOP,
                'listing_type' => ListingTypeEnum::RENT,
                'status' => PropertyStatusEnum::AVAILABLE,
                'price' => 2500000,
                'currency' => CurrencyEnum::SYP,
                'area' => 65,
                'address' => 'حلب، العزيزية',
                'contact_phone' => '0945000002',
            ],
            [
                'title' => 'أرض للبيع في اللاذقية',
                'description' => 'أرض مناسبة للاستثمار والبناء.',
                'property_type' => PropertyTypeEnum::LAND,
                'listing_type' => ListingTypeEnum::SALE,
                'status' => PropertyStatusEnum::PENDING_APPROVAL,
                'price' => 120000,
                'currency' => CurrencyEnum::USD,
                'area' => 500,
                'address' => 'اللاذقية، ضواحي المدينة',
                'contact_phone' => '0945000003',
            ],
        ];

        foreach ($properties as $index => $propertyData) {
            Property::firstOrCreate(
                ['title' => $propertyData['title']],
                array_merge($propertyData, [
                    'office_id' => $offices[$index % $offices->count()]->id,
                    'owner_id' => $owners[$index % $owners->count()]->id,
                    'governorate_id' => $governorates[$index % $governorates->count()]->id,
                    'commission_rate' => null,
                    'owner_notes' => null,
                    'approved_at' => $propertyData['status'] === PropertyStatusEnum::AVAILABLE
                        ? now()
                        : null,
                ])
            );
        }
    }
}
