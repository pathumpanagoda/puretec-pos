<?php

namespace App\Http\Controllers\Puretec;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Audit/Activity log viewer.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user:id,name,email,role'])
            ->latest();

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->paginate(50);

        // Get unique actions for filter dropdown
        $actions = ActivityLog::distinct()->pluck('action')->sort();

        return view('puretec.audit-log', compact('logs', 'actions'));
    }
}
