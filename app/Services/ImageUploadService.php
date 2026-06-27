<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    public function storeListingImages(Listing $listing, array $files, int $primaryIndex = 0): Collection
    {
        $images = collect();

        if ($files === []) {
            return $images;
        }

        $primaryIndex = min(max(0, $primaryIndex), count($files) - 1);
        $nextPos = ($listing->images()->max('position') ?? 0) + 1;

        $listing->images()->update(['is_primary' => false]);

        foreach ($files as $index => $file) {
            $path = $this->store($file, "listings/{$listing->id}");

            $image = ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'position' => $nextPos + $index,
                'is_primary' => $index === $primaryIndex,
            ]);

            $images->push($image);
        }

        return $images;
    }

    /**
     * @param  array<int, UploadedFile>  $newFiles
     * @return array<int, ListingImage>
     */
    public function uploadNewImages(Listing $listing, array $newFiles): array
    {
        $created = [];

        foreach ($newFiles as $index => $file) {
            $path = $this->store($file, "listings/{$listing->id}");

            $created[$index] = ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'position' => 9000 + $index,
                'is_primary' => false,
            ]);
        }

        return $created;
    }

    /**
     * @param  array<int, string>  $imageOrder  Tokens like e:12 or n:0
     * @param  array<int, ListingImage>  $newImages
     */
    public function applyImageOrder(Listing $listing, array $imageOrder, string $primaryImage, array $newImages = []): void
    {
        $listing->images()->update(['is_primary' => false]);

        foreach ($imageOrder as $position => $token) {
            $image = $this->resolveImageToken($listing, $token, $newImages);

            if ($image === null) {
                continue;
            }

            $image->update([
                'position' => $position + 1,
                'is_primary' => $token === $primaryImage,
            ]);
        }
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

    private function resolveImageToken(Listing $listing, string $token, array $newImages): ?ListingImage
    {
        if (! str_contains($token, ':')) {
            return null;
        }

        [$type, $ref] = explode(':', $token, 2);

        if ($type === 'e') {
            return ListingImage::query()
                ->where('listing_id', $listing->id)
                ->find((int) $ref);
        }

        if ($type === 'n') {
            return $newImages[(int) $ref] ?? null;
        }

        return null;
    }

    private function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }
}
