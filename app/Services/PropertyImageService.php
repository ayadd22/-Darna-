<?php

namespace App\Services;

use App\Contracts\PropertyImageServiceInterface;
use App\Models\Image;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


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

    $path = $image->path;

    DB::transaction(function () use ($image, $path) {
        $image->delete();

        DB::afterCommit(function () use ($path) {
            Storage::disk(self::DISK)->delete($path);
        });
     });
  }
  public function deleteAll(Property $property): void
  {
    $paths = $property->images()->pluck('path')->all();

    DB::transaction(function () use ($property, $paths) {
        $property->images()->delete();

        DB::afterCommit(function () use ($paths) {
            foreach ($paths as $path) {
                Storage::disk(self::DISK)->delete($path);
            }
        });
     });
   }
}
