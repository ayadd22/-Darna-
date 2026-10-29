<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'owner_id',
        'governorate_id',
        'title',
        'description',
        'property_type',
        'listing_type',
        'status',
        'price',
        'currency',
        'area',
        'address',
        'contact_phone',
        'commission_rate',
        'owner_notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyTypeEnum::class,
            'listing_type' => ListingTypeEnum::class,
            'status' => PropertyStatusEnum::class,
            'currency' => CurrencyEnum::class,
            'price' => 'decimal:2',
            'area' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

 
    public function scopeForGovernorate(Builder $query, int $governorateId): Builder
    {
        return $query->where('governorate_id', $governorateId);
    }

    public function scopeOfListingType(Builder $query, ListingTypeEnum $type): Builder
    {
        return $query->where('listing_type', $type->value);
    }


    public function scopeOfPropertyType(Builder $query, PropertyTypeEnum $type): Builder
    {
        return $query->where('property_type', $type->value);
    }

    public function scopeMinPrice(Builder $query, float|int $min): Builder
    {
        return $query->where('price', '>=', $min);
    }

    public function scopeMaxPrice(Builder $query, float|int $max): Builder
    {
        return $query->where('price', '<=', $max);
    }



    public function scopeMinArea(Builder $query, float|int $min): Builder
    {
        return $query->where('area', '>=', $min);
    }

    public function scopeMaxArea(Builder $query, float|int $max): Builder
    {
        return $query->where('area', '<=', $max);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
