<?php

namespace App\Services;

use App\Cache\PropertyCacheKeys;
use App\Contracts\PropertyServiceInterface;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyStatusEnum;
use App\Enums\PropertyTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Contracts\PropertyImageServiceInterface;


class PropertyService implements PropertyServiceInterface
{
    

    public function __construct(
    private readonly PropertyImageServiceInterface $propertyImageService
     ) {}

    public function create(array $validatedData, User $user): Property
    {
        if ($user->role === UserRoleEnum::OFFICE) {
            $validatedData['office_id'] = $user->office_id;
        }

        $validatedData['status'] = PropertyStatusEnum::PENDING_APPROVAL;

        $property = Property::create($validatedData);

        $this->invalidateCollectionCaches();

        return $property;
    }

 
    public function list(?User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Property::query()
            ->with(['office', 'owner', 'governorate']);

        
        if (! $user || $user->role === UserRoleEnum::USER) {
            $query->where('status', PropertyStatusEnum::AVAILABLE)
                ->whereNotNull('approved_at');
        } elseif ($user->role === UserRoleEnum::OFFICE) {
            $query->where('office_id', $user->office_id);
        }
       

    
        if (isset($filters['governorate_id'])) {
            $query->forGovernorate((int) $filters['governorate_id']);
        }
        if (isset($filters['listing_type'])) {
            $query->ofListingType(ListingTypeEnum::from($filters['listing_type']));
        }
        if (isset($filters['property_type'])) {
            $query->ofPropertyType(PropertyTypeEnum::from($filters['property_type']));
        }
        if (isset($filters['min_price'])) {
            $query->minPrice($filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->maxPrice($filters['max_price']);
        }

        if (isset($filters['min_area'])) {
           $query->minArea($filters['min_area']);
        }

        if (isset($filters['max_area'])) {
           $query->maxArea($filters['max_area']);
        }

        return $query->latest()->paginate(15);
    }


    public function show(?User $user, Property $property): Property
    {
       
        if (! $user || $user->role === UserRoleEnum::USER) {
            abort_unless(
                $property->status === PropertyStatusEnum::AVAILABLE
                    && $property->approved_at !== null,
                404
            );
        } elseif ($user->role === UserRoleEnum::OFFICE) {
            abort_unless($user->office_id === $property->office_id, 404);
        }
      
        $loaded = Cache::remember(
            PropertyCacheKeys::detail($property->id),
            PropertyCacheKeys::TTL_DETAIL,
            fn () => $property->load(['office', 'owner', 'governorate', 'images'])
        );

        return $loaded;
    }



    public function update(array $validatedData, Property $property): Property
    {
        $property->update($validatedData);

      
        $this->invalidatePropertyCaches($property->id);

        return $property->refresh();
    }

  

  public function destroy(User $user, Property $property): void
  {
    abort_unless($user->can('delete', $property), 403);

    $id = $property->id;

    DB::transaction(function () use ($property) {
        $this->propertyImageService->deleteAll($property);

        $property->delete();
    });

    $this->invalidatePropertyCaches($id);
  }  

 

    public function approve(User $user, Property $property): Property
    {
        abort_unless($user->can('approve', Property::class), 403);

        abort_unless(
            $property->status === PropertyStatusEnum::PENDING_APPROVAL,
            422,
            'لا يمكن الموافقة على هذا العقار لأنه ليس في حالة انتظار الموافقة.'
        );

        $property->update([
            'status'      => PropertyStatusEnum::AVAILABLE,
            'approved_at' => now(),
        ]);

     
        $this->invalidatePropertyCaches($property->id);

        return $property->refresh();
    }


    public function reject(User $user, Property $property): Property
    {
        abort_unless($user->can('approve', Property::class), 403);

        abort_unless(
            $property->status === PropertyStatusEnum::PENDING_APPROVAL,
            422,
            'لا يمكن رفض هذا العقار لأنه ليس في حالة انتظار الموافقة.'
        );

        $property->update([
            'status'      => PropertyStatusEnum::REJECTED,
            'approved_at' => null,
        ]);

        
        $this->invalidatePropertyCaches($property->id);

        return $property->refresh();
    }

    public function featuredProperties(int $limit = 6): Collection
    {
    $limitsKey = PropertyCacheKeys::FEATURED_LIMITS;

    $cachedLimits = Cache::get($limitsKey, []);

    if (! in_array($limit, $cachedLimits, true)) {
        $cachedLimits[] = $limit;

        Cache::forever($limitsKey, $cachedLimits);
    }

    return Cache::remember(
        PropertyCacheKeys::featured($limit),
        PropertyCacheKeys::TTL_FEATURED,
        fn () => Property::query()
            ->with(['office', 'owner', 'governorate'])
            ->where('status', PropertyStatusEnum::AVAILABLE)
            ->whereNotNull('approved_at')
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
      );
   }


    public function governorateStats(): Collection
    {
        return Cache::remember(
            PropertyCacheKeys::governorateStats(),
            PropertyCacheKeys::TTL_GOVERNORATE_STATS,
           fn () => DB::table('properties')
            ->select('governorate_id', DB::raw('COUNT(*) as total'))
            ->where('status', PropertyStatusEnum::AVAILABLE->value)
            ->whereNotNull('approved_at')
            ->groupBy('governorate_id')
            ->orderBy('governorate_id')
            ->get()
        );
    }

  
    private function invalidatePropertyCaches(int $propertyId): void
    {
        Cache::forget(PropertyCacheKeys::detail($propertyId));
        $this->invalidateCollectionCaches();
    }

  
    private function invalidateCollectionCaches(): void
   {
    $limits = Cache::get(PropertyCacheKeys::FEATURED_LIMITS, []);

    foreach ($limits as $limit) {
        Cache::forget(PropertyCacheKeys::featured($limit));
    }

    Cache::forget(PropertyCacheKeys::FEATURED_LIMITS);
    Cache::forget(PropertyCacheKeys::governorateStats());
   }
}
