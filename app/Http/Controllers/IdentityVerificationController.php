<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentityVerification\StoreVerificationRequest;
use App\Services\IdentityVerificationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IdentityVerificationController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedIdentity()) {
            return redirect()
                ->route('account.index')
                ->with('success', 'Tu identidad ya está verificada.');
        }

        if ($user->identityVerificationStatus() === 'pending') {
            return redirect()
                ->route('account.index')
                ->with('success', 'Tu documento ya está en revisión. Te avisaremos cuando sea aprobado.');
        }

        return Inertia::render('Account/VerifyIdentity');
    }

    public function store(
        StoreVerificationRequest $request,
        IdentityVerificationService $service,
    ): RedirectResponse {
        $service->submit(
            $request->user(),
            $request->validated('document_type'),
            $request->file('document'),
        );

        return redirect()
            ->route('account.index')
            ->with('success', 'Documento enviado. Te notificaremos cuando sea revisado por nuestro equipo.');
    }
}
