<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdentityVerification\StoreVerificationRequest;
use App\Services\IdentityVerificationService;
use Illuminate\Http\JsonResponse;

class IdentityVerificationController extends Controller
{
    public function store(StoreVerificationRequest $request, IdentityVerificationService $service): JsonResponse
    {
        $service->submit(
            $request->user(),
            $request->validated('document_type'),
            $request->file('document_front'),
            $request->file('document_back'),
        );

        return response()->json(['message' => 'Documento enviado. Te notificaremos cuando sea revisado.'], 201);
    }
}
