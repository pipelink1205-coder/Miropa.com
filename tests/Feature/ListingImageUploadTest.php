<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): array
    {
        $category = Category::query()->where('slug', 'calzado-tenis-y-zapatillas')->firstOrFail();
        $condition = Condition::factory()->create();

        return compact('category', 'condition');
    }

    public function test_web_create_uses_primary_index_for_cover_image(): void
    {
        Storage::fake('public');

        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/listings', [
                'category_id' => $cat->id,
                'condition_id' => $cond->id,
                'title' => 'Zapatillas Nike talla 40',
                'description' => 'Par de zapatillas en excelente estado, poco uso en ciudad.',
                'price' => 180000,
                'status' => 'active',
                'primary_index' => 1,
                'images' => [
                    UploadedFile::fake()->image('primera.jpg'),
                    UploadedFile::fake()->image('portada.jpg'),
                ],
            ])
            ->assertRedirect();

        $listing = Listing::query()->where('title', 'Zapatillas Nike talla 40')->firstOrFail();
        $images = $listing->images()->orderBy('position')->get();

        $this->assertCount(2, $images);
        $this->assertFalse($images[0]->is_primary);
        $this->assertTrue($images[1]->is_primary);
    }

    public function test_web_update_can_reorder_and_change_primary_image(): void
    {
        Storage::fake('public');

        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $first = ListingImage::factory()->create([
            'listing_id' => $listing->id,
            'path' => 'listings/a.jpg',
            'position' => 1,
            'is_primary' => true,
        ]);
        $second = ListingImage::factory()->create([
            'listing_id' => $listing->id,
            'path' => 'listings/b.jpg',
            'position' => 2,
            'is_primary' => false,
        ]);

        Storage::disk('public')->put($first->path, 'a');
        Storage::disk('public')->put($second->path, 'b');

        $this->actingAs($user)
            ->put("/listings/{$listing->id}", [
                'image_order' => ["e:{$second->id}", "e:{$first->id}"],
                'primary_image' => "e:{$second->id}",
            ])
            ->assertRedirect();

        $first->refresh();
        $second->refresh();

        $this->assertFalse($first->is_primary);
        $this->assertTrue($second->is_primary);
        $this->assertSame(1, $second->position);
        $this->assertSame(2, $first->position);
    }
}
