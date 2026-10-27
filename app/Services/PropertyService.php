<?php

namespace App\Services;

use App\Contracts\PropertyServiceInterface;
use App\Enums\PropertyStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Property;
use App\Models\User;

class PropertyService implements PropertyServiceInterface
{
    public function create(array $validatedData, User $user): Property
    {
        if ($user->role === UserRoleEnum::OFFICE) {
            $validatedData['office_id'] = $user->office_id;
        }

        $validatedData['status'] = PropertyStatusEnum::PENDING_APPROVAL;

        return Property::create($validatedData);
    }
}
