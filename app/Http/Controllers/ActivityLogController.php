<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // ── Search ─────────────────────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        // ── Filters ────────────────────────────────────────────────────────────
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // ── Sidebar data ───────────────────────────────────────────────────────
        $modules = ActivityLog::distinct()->orderBy('module')->pluck('module');
        $users   = User::orderBy('name')->get(['id', 'name', 'role']);

        // ── Today's summary counts ─────────────────────────────────────────────
        $todayCounts = ActivityLog::selectRaw(
                "SUM(action = 'created') as created,
                 SUM(action = 'updated') as updated,
                 SUM(action = 'deleted') as deleted,
                 COUNT(*) as total"
            )
            ->whereDate('created_at', today())
            ->first();

        return view('activity-logs.index', compact('logs', 'modules', 'users', 'todayCounts'));
    }
}
