<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    private const TYPE_MAP = [
        'listing' => Listing::class,
        'user' => User::class,
        'message' => Message::class,
    ];

    public function store(StoreReportRequest $request): JsonResponse
    {
        $morphType = self::TYPE_MAP[$request->reportable_type];

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $morphType,
            'reportable_id' => $request->reportable_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json(['message' => 'Reporte enviado. Lo revisaremos pronto.'], 201);
    }
}
