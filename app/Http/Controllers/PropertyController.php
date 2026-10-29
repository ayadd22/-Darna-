<?php

namespace App\Http\Controllers;

use App\Contracts\PropertyServiceInterface;
use App\Http\Requests\ApprovePropertyRequest;
use App\Http\Requests\FilterPropertyRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyServiceInterface $propertyService,
    ) {}

    public function index(FilterPropertyRequest $request): AnonymousResourceCollection
    {
        $properties = $this->propertyService->list(
            $request->user(),
            $request->validated(),
        );

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
        $property = $this->propertyService->show($request->user(), $property);

        return new PropertyResource($property);
    }

    public function update(UpdatePropertyRequest $request, Property $property): PropertyResource
    {
        $property = $this->propertyService->update($request->validated(), $property);

        return new PropertyResource($property);
    }

    public function destroy(Request $request, Property $property): JsonResponse
    {
        $this->propertyService->destroy($request->user(), $property);

        return response()->json([
            'message' => 'تم حذف العقار بنجاح.',
        ]);
    }

    public function approve(ApprovePropertyRequest $request, Property $property): PropertyResource
    {
        $property = $this->propertyService->approve($request->user(), $property);

        return new PropertyResource($property);
    }

    public function reject(ApprovePropertyRequest $request, Property $property): PropertyResource
    {
        $property = $this->propertyService->reject($request->user(), $property);

        return new PropertyResource($property);
    }

   
    public function featured(): AnonymousResourceCollection
    {
        $properties = $this->propertyService->featuredProperties();

        return PropertyResource::collection($properties);
    }


    public function governorateStats(): JsonResponse
    {
        $stats = $this->propertyService->governorateStats();

        return response()->json(['data' => $stats->values()]);
    }
}
