<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(20);
        
        // Get unique actions for filter
        $actions = ActivityLog::distinct()->pluck('action');

        return view('admin.logs.index', compact('logs', 'actions'));
    }

    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        return view('admin.logs.show', compact('log'));
    }

    public function clear(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:DELETE'
        ]);

        $count = ActivityLog::count();
        ActivityLog::truncate();

        ActivityLog::log('delete', "Menghapus {$count} activity log");

        return redirect()->route('admin.ppdb.logs.index')
            ->with('success', "Berhasil menghapus {$count} activity log");
    }
}
