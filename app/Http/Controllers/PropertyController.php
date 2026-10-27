<?php

namespace App\Http\Controllers;

use App\Contracts\PropertyServiceInterface;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Enums\UserRoleEnum;
use App\Enums\PropertyStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdatePropertyRequest;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyServiceInterface $propertyService,
    ) {}

    
public function index(Request $request): AnonymousResourceCollection
  {
    $query = Property::query()
        ->with(['office', 'owner', 'governorate']);

    $user = $request->user();

    if (! $user || $user->role === UserRoleEnum::USER) {
        $query->where('status', PropertyStatusEnum::AVAILABLE)
            ->whereNotNull('approved_at');
    } elseif ($user->role === UserRoleEnum::OFFICE) {
        $query->where('office_id', $user->office_id);
    }

    $properties = $query->latest()->paginate(15);

    return PropertyResource::collection($properties);
   }


    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->propertyService->create(
            $request->validated(),
            $request->user(),
        );

        return (new PropertyResource($property))
            ->response()
            ->setStatusCode(201);
    }


    
  public function show(Request $request, Property $property): PropertyResource
   {
    $user = $request->user();

    if (! $user || $user->role === UserRoleEnum::USER) {
        abort_unless(
            $property->status === PropertyStatusEnum::AVAILABLE
                && $property->approved_at !== null,
            404
        );
    } elseif ($user->role === UserRoleEnum::OFFICE) {
        abort_unless($user->office_id === $property->office_id, 404);
    }

    $property->load(['office', 'owner', 'governorate', 'images']);

    return new PropertyResource($property);
  }

  
   public function update( UpdatePropertyRequest $request,Property $property): PropertyResource {
          $property->update($request->validated());

    return new PropertyResource($property->refresh());
   }
 


   

public function destroy(Property $property): JsonResponse
  {
    abort_unless(
        auth()->user()->can('delete', $property),
        403
     );

    $property->delete();

    return response()->json([
        'message' => 'تم حذف العقار بنجاح.',
    ]);
  }


}
