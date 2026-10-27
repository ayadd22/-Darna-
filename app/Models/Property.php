<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
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
