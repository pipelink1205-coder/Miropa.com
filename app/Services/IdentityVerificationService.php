<?php

namespace App\Services;

use App\Models\IdentityVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class IdentityVerificationService
{
    public function hasActiveVerification(User $user): bool
    {
        return IdentityVerification::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    /**
     * @throws ValidationException
     */
    public function submit(
        User $user,
        string $documentType,
        UploadedFile $documentFront,
        UploadedFile $documentBack,
    ): IdentityVerification {
        if ($this->hasActiveVerification($user)) {
            throw ValidationException::withMessages([
                'document_front' => 'Ya tienes una verificación pendiente o aprobada.',
            ]);
        }

        return IdentityVerification::create([
            'user_id' => $user->id,
            'document_type' => $documentType,
            'document_path' => $documentFront->store('identity-docs', 'local'),
            'document_back_path' => $documentBack->store('identity-docs', 'local'),
            'status' => 'pending',
        ]);
    }
}
