<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any properties.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRoleEnum::ADMIN,
            UserRoleEnum::OFFICE,
        ], true);
    }

    /**
     * Determine whether the user can view a property.
     */
    public function view(User $user, Property $property): bool
    {
        if ($user->role === UserRoleEnum::ADMIN) {
            return true;
        }

        if ($user->role === UserRoleEnum::OFFICE) {
            return $user->office_id === $property->office_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create properties.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRoleEnum::ADMIN,
            UserRoleEnum::OFFICE,
        ], true);
    }

    /**
     * Determine whether the user can update a property.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->role === UserRoleEnum::ADMIN) {
            return true;
        }

        if ($user->role === UserRoleEnum::OFFICE) {
            return $user->office_id === $property->office_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a property.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->role === UserRoleEnum::ADMIN) {
            return true;
        }

        if ($user->role === UserRoleEnum::OFFICE) {
            return $user->office_id === $property->office_id;
        }

        return false;
    }

     public function approve(User $user): bool
    {
        return $user->role === UserRoleEnum::ADMIN;
    }
}
