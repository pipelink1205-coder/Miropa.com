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

class ListingTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): array
    {
        $category = Category::factory()->create(['slug' => 'electronica']);
        $condition = Condition::factory()->create();

        return compact('category', 'condition');
    }

    public function test_authenticated_user_can_create_listing(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', [
                'category_id' => $cat->id,
                'condition_id' => $cond->id,
                'title' => 'iPhone 13 negro usado',
                'description' => 'Teléfono en perfecto estado, sin rayones, cargador incluido.',
                'price' => 1800000,
                'status' => 'active',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'iPhone 13 negro usado');

        $this->assertDatabaseHas('listings', ['title' => 'iPhone 13 negro usado']);
    }

    public function test_unauthenticated_user_cannot_create_listing(): void
    {
        $this->postJson('/api/v1/listings', [])->assertStatus(401);
    }

    public function test_listing_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', ['title' => 'x'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'condition_id', 'description', 'price']);
    }

    public function test_owner_can_update_listing(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/listings/{$listing->id}", ['price' => 999000])
            ->assertOk()
            ->assertJson(['data' => ['price' => 999000]]);
    }

    public function test_non_owner_cannot_update_listing(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $this->actingAs($other, 'sanctum')
            ->putJson("/api/v1/listings/{$listing->id}", ['price' => 1])
            ->assertStatus(403);
    }

    public function test_owner_can_delete_listing(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/listings/{$listing->id}")
            ->assertOk();

        $this->assertSoftDeleted('listings', ['id' => $listing->id]);
    }

    public function test_listings_are_publicly_accessible(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        Listing::factory(5)->create([
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/listings')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_owner_can_upload_listing_images(): void
    {
        Storage::fake('public');

        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->post("/api/v1/listings/{$listing->id}/images", [
                'images' => [
                    UploadedFile::fake()->image('phone.jpg', 800, 600),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseCount('listing_images', 1);
        $this->assertDatabaseHas('listing_images', [
            'listing_id' => $listing->id,
            'is_primary' => true,
        ]);
    }

    public function test_owner_can_remove_listing_images_on_update(): void
    {
        Storage::fake('public');

        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $image = ListingImage::factory()->create([
            'listing_id' => $listing->id,
            'path' => 'listings/test-photo.jpg',
            'is_primary' => true,
        ]);

        Storage::disk('public')->put($image->path, 'fake-image-content');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/listings/{$listing->id}", [
                'remove_image_ids' => [$image->id],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('listing_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_authenticated_owner_can_access_edit_page(): void
    {
        ['category' => $cat, 'condition' => $cond] = $this->seedBaseData();
        $user = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'condition_id' => $cond->id,
        ]);

        $this->actingAs($user)
            ->get("/listings/{$listing->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Listings/Edit')
                ->has('listing', fn ($prop) => $prop
                    ->where('id', $listing->id)
                    ->where('title', $listing->title)
                    ->etc()
                )
            );
    }
}
