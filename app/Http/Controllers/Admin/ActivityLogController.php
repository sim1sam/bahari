<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->latest('created_at');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.activity-logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'staffUsers' => User::staff()->orderBy('name')->get(['id', 'name', 'email']),
            'actions' => $actions,
            'filters' => [
                'search' => $search ?? '',
                'user_id' => $userId ?? '',
                'action' => $action ?? '',
                'from' => $from ?? '',
                'to' => $to ?? '',
            ],
            'stats' => [
                'total' => ActivityLog::query()->count(),
                'today' => ActivityLog::query()->whereDate('created_at', today())->count(),
                'users' => ActivityLog::query()->whereNotNull('user_id')->distinct()->count('user_id'),
            ],
        ]);
    }
}
