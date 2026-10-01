<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('reports.view');
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $from = CarbonImmutable::parse($validated['date_from'] ?? today()->startOfMonth()->toDateString())->startOfDay();
        $to = CarbonImmutable::parse($validated['date_to'] ?? today()->toDateString())->endOfDay();

        $base = Order::query()->where('status', OrderStatus::Completed)
            ->whereBetween('completed_at', [$from, $to]);
        $orders = (clone $base)->with(['customer', 'channel'])->latest('completed_at')->paginate(20)->withQueryString();

        $paymentBreakdown = Payment::query()
            ->selectRaw('payment_method_id, SUM(amount) as total')
            ->with('paymentMethod')
            ->where('status', 'paid')
            ->whereHas('order', fn (Builder $query) => $query->where('status', OrderStatus::Completed)->whereBetween('completed_at', [$from, $to]))
            ->groupBy('payment_method_id')->get();

        return view('reports.sales', [
            'orders' => $orders,
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'totalSales' => (float) (clone $base)->sum('grand_total'),
            'transactionCount' => (clone $base)->count(),
            'averageSale' => (float) (clone $base)->avg('grand_total'),
            'paymentBreakdown' => $paymentBreakdown,
        ]);
    }
}
