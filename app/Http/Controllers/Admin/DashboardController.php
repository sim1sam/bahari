<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
                'customers' => User::customers()->count(),
                'revenue' => (float) Order::sum('total'),
                'paid_revenue' => (float) Order::sum('amount_paid'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'today_orders' => Order::where('created_at', '>=', $today)->count(),
                'today_revenue' => (float) Order::where('created_at', '>=', $today)->sum('total'),
            ],
            'ordersByStatus' => Order::query()
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
            'recentOrders' => Order::query()->latest()->take(8)->get(),
        ]);
    }
}
