<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = IntegrationLog::query();

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(reference_id) LIKE ?', ["%{$clean}%"])
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

        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.integration_logs.index', compact('logs'));
    }
}
