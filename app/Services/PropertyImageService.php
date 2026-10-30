<?php

namespace App\Services;

use App\Contracts\PropertyImageServiceInterface;
use App\Models\Image;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyImageService implements PropertyImageServiceInterface
{
  
    private const DISK = 'public';

    public function store(
        Property $property,
        UploadedFile $file,
        ?string $altText = null,
        int $sortOrder = 0,
    ): Image {
       
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename  = Str::uuid()->toString() . '.' . $extension;
        $directory = "properties/{$property->id}";
        $path = $file->storeAs($directory, $filename, self::DISK);

          if (! $path) {
             throw new \RuntimeException('فشل حفظ صورة العقار.');
            }

        return $property->images()->create([
            'path'       => $path,
            'alt_text'   => $altText,
            'sort_order' => $sortOrder,
        ]);
    }

    public function destroy(Property $property, Image $image, User $user): void
    {
        abort_unless(
            $image->imageable_type === Property::class
                && $image->imageable_id === $property->id,
            404
        );
        abort_unless($user->can('update', $property), 403);

        if (Storage::disk(self::DISK)->exists($image->path)) {
            Storage::disk(self::DISK)->delete($image->path);
        }

        $image->delete();
    }

   public function deleteAll(Property $property): void
  {
    $images = $property->images;

    foreach ($images as $image) {
        if (Storage::disk(self::DISK)->exists($image->path)) {
            Storage::disk(self::DISK)->delete($image->path);
        }

        $image->delete();
    }
  }

}
