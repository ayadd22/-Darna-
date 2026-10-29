<?php

namespace App\Contracts;

use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PropertyServiceInterface
{
    public function create(array $validatedData, User $user): Property;
    public function list(?User $user, array $filters = []): LengthAwarePaginator;
    public function show(?User $user, Property $property): Property;
    public function update(array $validatedData, Property $property): Property;
    public function destroy(User $user, Property $property): void;
    public function approve(User $user, Property $property): Property;
    public function reject(User $user, Property $property): Property;
    public function featuredProperties(int $limit = 6): Collection;
    public function governorateStats(): Collection;
}
