<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdentityVerification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function reports(Request $request): JsonResponse
    {
        $reports = Report::with(['reporter', 'reportable'])
            ->where('status', $request->get('status', 'open'))
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $reports]);
    }

    public function resolveReport(Request $request, Report $report): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:reviewing,resolved,dismissed'],
            'action' => ['nullable', 'string'],
        ]);

        $report->update([
            'status' => $request->status,
            'resolved_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Reporte actualizado.']);
    }

    public function verifications(Request $request): JsonResponse
    {
        $verifications = IdentityVerification::with('user')
            ->where('status', $request->get('status', 'pending'))
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $verifications]);
    }

    public function reviewVerification(Request $request, IdentityVerification $verification): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:approved,rejected']]);

        $verification->update([
            'status' => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($request->status === 'approved') {
            $verification->user->update([
                'verification_level' => 'id_document',
                'is_verified' => true,
            ]);
        }

        return response()->json(['message' => 'Verificación actualizada.']);
    }

    public function updateUserStatus(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:active,suspended,banned'],
        ]);

        $user->update(['status' => $request->status]);

        return response()->json(['message' => "Usuario {$request->status}."]);
    }
}
