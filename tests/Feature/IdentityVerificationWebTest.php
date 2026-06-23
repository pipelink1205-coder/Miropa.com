<?php

namespace Tests\Feature;

use App\Models\IdentityVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdentityVerificationWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_identity_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/cuenta/verificar-identidad')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Account/VerifyIdentity'));
    }

    public function test_verified_user_is_redirected_from_identity_form(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'verification_level' => 'id_document',
        ]);

        IdentityVerification::create([
            'user_id' => $user->id,
            'document_type' => 'cedula',
            'document_path' => 'identity-docs/front.jpg',
            'document_back_path' => 'identity-docs/back.jpg',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get('/cuenta/verificar-identidad')
            ->assertRedirect(route('account.index'));
    }

    public function test_user_can_submit_identity_document_via_web(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post('/cuenta/verificar-identidad', [
                'document_type' => 'cedula',
                'document_front' => UploadedFile::fake()->image('cedula-frente.jpg'),
                'document_back' => UploadedFile::fake()->image('cedula-reverso.jpg'),
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHas('success');

        $verification = IdentityVerification::where('user_id', $user->id)->first();

        $this->assertNotNull($verification);
        $this->assertSame('cedula', $verification->document_type);
        $this->assertSame('pending', $verification->status);
        $this->assertNotNull($verification->document_path);
        $this->assertNotNull($verification->document_back_path);
        Storage::disk('local')->assertExists($verification->document_path);
        Storage::disk('local')->assertExists($verification->document_back_path);
    }

    public function test_user_cannot_submit_without_both_sides(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->from('/cuenta/verificar-identidad')
            ->post('/cuenta/verificar-identidad', [
                'document_type' => 'cedula',
                'document_front' => UploadedFile::fake()->image('cedula-frente.jpg'),
            ])
            ->assertRedirect('/cuenta/verificar-identidad')
            ->assertSessionHasErrors('document_back');
    }

    public function test_user_cannot_submit_duplicate_identity_via_web(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['email_verified_at' => now()]);

        IdentityVerification::create([
            'user_id' => $user->id,
            'document_type' => 'cedula',
            'document_path' => 'identity-docs/front.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->from('/cuenta/verificar-identidad')
            ->post('/cuenta/verificar-identidad', [
                'document_type' => 'cedula',
                'document_front' => UploadedFile::fake()->image('cedula-frente.jpg'),
                'document_back' => UploadedFile::fake()->image('cedula-reverso.jpg'),
            ])
            ->assertRedirect('/cuenta/verificar-identidad')
            ->assertSessionHasErrors('document_front');
    }
}
