<?php

namespace App\Http\Controllers;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdentityDocumentController extends Controller
{
    public function show(IdentityVerification $verification, string $side): StreamedResponse
    {
        Gate::authorize('admin');

        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $path = $side === 'front'
            ? $verification->document_path
            : $verification->document_back_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
