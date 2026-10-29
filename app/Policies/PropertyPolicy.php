<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
   
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRoleEnum::ADMIN,
            UserRoleEnum::OFFICE,
        ], true);
    }

   
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

   
    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRoleEnum::ADMIN,
            UserRoleEnum::OFFICE,
        ], true);
    }

    
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
