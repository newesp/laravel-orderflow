<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminIntegrationLogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = IntegrationLog::query();

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(CAST(reference_id AS TEXT)) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(target) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(event_type) LIKE ?', ["%{$clean}%"]);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($eventType = $request->input('event_type')) {
            $query->where('event_type', $eventType);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
