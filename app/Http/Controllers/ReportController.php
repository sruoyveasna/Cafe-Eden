<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $top = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('menu_item_id')
            ->with('menuItem')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return response()->json($top);
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
