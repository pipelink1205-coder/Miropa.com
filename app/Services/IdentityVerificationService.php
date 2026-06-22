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
    public function submit(User $user, string $documentType, UploadedFile $document): IdentityVerification
    {
        if ($this->hasActiveVerification($user)) {
            throw ValidationException::withMessages([
                'document' => 'Ya tienes una verificación pendiente o aprobada.',
            ]);
        }

        $path = $document->store('identity-docs', 'local');

        return IdentityVerification::create([
            'user_id' => $user->id,
            'document_type' => $documentType,
            'document_path' => $path,
            'status' => 'pending',
        ]);
    }
}
