<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today();

        return view('dashboard', [
            'ordersToday' => Order::query()->whereDate('ordered_at', $today)->count(),
            'processingOrders' => Order::query()->where('status', OrderStatus::Processing)->count(),
            'completedToday' => Order::query()->where('status', OrderStatus::Completed)->whereDate('completed_at', $today)->count(),
            'salesToday' => (float) Order::query()->where('status', OrderStatus::Completed)->whereDate('completed_at', $today)->sum('grand_total'),
            'lowStockCount' => Product::query()->whereColumn('current_stock', '<=', 'minimum_stock')->where('is_active', true)->count(),
            'recentOrders' => Order::query()->with(['customer', 'channel'])->latest('ordered_at')->limit(8)->get(),
            'lowStockProducts' => Product::query()->with('unit')->whereColumn('current_stock', '<=', 'minimum_stock')->where('is_active', true)->orderBy('current_stock')->limit(6)->get(),
        ]);
    }
}
