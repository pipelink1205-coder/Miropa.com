<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    public function storeListingImages(Listing $listing, array $files): Collection
    {
        $images = collect();
        $nextPos = $listing->images()->max('position') + 1;
        $hasPrimary = $listing->images()->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            $path = $this->store($file, "listings/{$listing->id}");

            $image = ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'position' => $nextPos + $index,
                'is_primary' => (! $hasPrimary && $index === 0),
            ]);

            if (! $hasPrimary && $index === 0) {
                $hasPrimary = true;
            }

            $images->push($image);
        }

        return $images;
    }

    public function deleteImage(ListingImage $image): void
    {
        Storage::disk('public')->delete($image->path);

        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $next = ListingImage::where('listing_id', $image->listing_id)
                ->orderBy('position')
                ->first();
            $next?->update(['is_primary' => true]);
        }
    }

    private function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }
}
