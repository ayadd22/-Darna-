<?php

namespace App\Contracts;

use App\Models\Property;
use App\Models\User;

interface PropertyServiceInterface
{
    /**
     * Create a new property on behalf of the given user.
     *
     * The implementation is responsible for:
     * - Deriving office_id from the authenticated user when the role is OFFICE.
     * - Setting the initial status to PropertyStatusEnum::PENDING_APPROVAL.
     *
     * @param  array<string, mixed>  $validatedData  Data already validated by StorePropertyRequest.
     * @param  User  $user  The authenticated user performing the creation.
     */
    public function create(array $validatedData, User $user): Property;
}
