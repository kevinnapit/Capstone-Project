<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today();
        $trendStart = CarbonImmutable::today()->subDays(6);
        $salesByDate = Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereBetween('completed_at', [$trendStart->startOfDay(), CarbonImmutable::today()->endOfDay()])
            ->get(['completed_at', 'grand_total'])
            ->groupBy(fn (Order $order): string => $order->completed_at->toDateString())
            ->map(fn ($orders): float => (float) $orders->sum('grand_total'));

        $statusCounts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $composition = OrderItem::query()
            ->selectRaw('type, SUM(subtotal) as total')
            ->whereHas('order', fn ($query) => $query
                ->where('status', OrderStatus::Completed)
                ->where('completed_at', '>=', CarbonImmutable::today()->subDays(29)->startOfDay()))
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('dashboard', [
            'ordersToday' => Order::query()->whereDate('ordered_at', $today)->count(),
            'processingOrders' => Order::query()->where('status', OrderStatus::Processing)->count(),
            'completedToday' => Order::query()->where('status', OrderStatus::Completed)->whereDate('completed_at', $today)->count(),
            'salesToday' => (float) Order::query()->where('status', OrderStatus::Completed)->whereDate('completed_at', $today)->sum('grand_total'),
            'lowStockCount' => Product::query()->whereColumn('current_stock', '<=', 'minimum_stock')->where('is_active', true)->count(),
            'recentOrders' => Order::query()->with(['customer', 'channel'])->latest('ordered_at')->limit(8)->get(),
            'lowStockProducts' => Product::query()->with('unit')->whereColumn('current_stock', '<=', 'minimum_stock')->where('is_active', true)->orderBy('current_stock')->limit(6)->get(),
            'salesChart' => [
                'labels' => collect(range(0, 6))->map(fn (int $day) => $trendStart->addDays($day)->format('d/m'))->all(),
                'series' => collect(range(0, 6))->map(fn (int $day): float => (float) ($salesByDate[$trendStart->addDays($day)->toDateString()] ?? 0))->all(),
            ],
            'statusChart' => [
                'labels' => collect(OrderStatus::cases())->map->label()->all(),
                'series' => collect(OrderStatus::cases())->map(fn (OrderStatus $status): int => (int) ($statusCounts[$status->value] ?? 0))->all(),
            ],
            'compositionChart' => [
                'labels' => ['Produk', 'Layanan'],
                'series' => [(float) ($composition['product'] ?? 0), (float) ($composition['service'] ?? 0)],
            ],
        ]);
    }
}
