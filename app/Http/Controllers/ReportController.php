<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MenuItem;

class ReportController extends Controller
{
    // 📅 Summary for today
    public function summary()
    {
        $today = Carbon::today();

        $orders = Order::whereDate('created_at', $today)->get();
        return response()->json([
            'date' => $today->toDateString(),
            'total_revenue' => $orders->sum('total_amount'),
            'order_count' => $orders->count(),
        ]);
    }

    // 📈 Top 5 best-selling items (all time)
    public function topItems()
    {
        $topItems = MenuItem::select('menu_items.name', DB::raw('SUM(order_items.quantity) as total_orders'))
            ->join('order_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->groupBy('menu_items.name')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        return response()->json($topItems);
    }

    // 📊 Weekly and monthly stats
    public function stats()
    {
        $weekly = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthly = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'weekly' => $weekly,
            'monthly' => $monthly
        ]);
    }
}
