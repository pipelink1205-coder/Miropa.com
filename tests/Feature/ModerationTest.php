<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\IdentityVerification;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeListing(User $seller): Listing
    {
        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_report_a_listing(): void
    {
        $seller = User::factory()->create();
        $reporter = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($reporter)
            ->postJson('/api/v1/reports', [
                'reportable_type' => 'listing',
                'reportable_id' => $listing->id,
                'reason' => 'spam',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Listing::class,
            'reportable_id' => $listing->id,
        ]);
    }

    public function test_unauthenticated_cannot_report(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->postJson('/api/v1/reports', [
            'reportable_type' => 'listing',
            'reportable_id' => $listing->id,
            'reason' => 'spam',
        ])->assertStatus(401);
    }

    public function test_user_can_submit_identity_verification(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/identity-verifications', [
                'document_type' => 'cedula',
                'document_front' => UploadedFile::fake()->image('cedula-frente.jpg'),
                'document_back' => UploadedFile::fake()->image('cedula-reverso.jpg'),
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('identity_verifications', [
            'user_id' => $user->id,
            'document_type' => 'cedula',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_submit_duplicate_verification(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        IdentityVerification::create([
            'user_id' => $user->id,
            'document_type' => 'cedula',
            'document_path' => 'identity-docs/test.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/identity-verifications', [
                'document_type' => 'cedula',
                'document_front' => UploadedFile::fake()->image('cedula-frente.jpg'),
                'document_back' => UploadedFile::fake()->image('cedula-reverso.jpg'),
            ])
            ->assertStatus(422);
    }

    public function test_admin_can_resolve_report(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reporter = User::factory()->create();
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => Listing::class,
            'reportable_id' => $listing->id,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/reports/{$report->id}/resolve", ['status' => 'resolved'])
            ->assertStatus(200);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'resolved']);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->getJson('/api/v1/admin/reports')
            ->assertStatus(403);
    }

    public function test_admin_can_approve_identity_verification(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $applicant = User::factory()->create(['is_verified' => false]);

        $verification = IdentityVerification::create([
            'user_id' => $applicant->id,
            'document_type' => 'cedula',
            'document_path' => 'identity-docs/test.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/identity-verifications/{$verification->id}/review", ['status' => 'approved'])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'is_verified' => true,
        ]);
    }
}
