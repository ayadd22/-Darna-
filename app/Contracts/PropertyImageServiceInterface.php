<?php

namespace App\Contracts;

use App\Models\Image;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface PropertyImageServiceInterface
{
    public function store(
        Property $property,
        UploadedFile $file,
        ?string $altText = null,
        int $sortOrder = 0,
    ): Image;

    public function destroy(
        Property $property,
        Image $image,
        User $user
    ): void;

    public function deleteAll(Property $property): void;
}
