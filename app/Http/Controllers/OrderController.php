<?php

namespace App\Http\Controllers;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\Orders\CreateDraftOrderAction;
use App\Enums\OrderStatus;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Http\Requests\StoreDraftOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('orders.view');

        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        $validStatus = OrderStatus::tryFrom((string) $status);

        $orders = Order::query()
            ->with(['customer', 'channel', 'creator'])
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($validStatus, fn (Builder $query) => $query->where('status', $validStatus->value))
            ->latest('ordered_at')
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'search' => $search,
            'selectedStatus' => $validStatus?->value,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('orders.create');

        $today = today();
        $servicePrices = ServicePrice::query()
            ->with(['serviceType', 'paperType', 'printMode'])
            ->where('is_active', true)
            ->whereHas('serviceType', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('paperType', fn (Builder $query) => $query->where('is_active', true))
            ->whereDate('effective_from', '<=', $today)
            ->where(fn (Builder $query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $today))
            ->get();

        $products = Product::query()->with('unit')->where('is_active', true)->orderBy('name')->get();

        return view('orders.create', [
            'channels' => OrderChannel::query()->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'productOptions' => $products->map(fn (Product $product): array => [
                'id' => (string) $product->id,
                'label' => "{$product->name} ({$product->sku}) — Rp ".number_format((float) $product->selling_price, 0, ',', '.')." — stok {$product->current_stock} {$product->unit->symbol}",
                'price' => (float) $product->selling_price,
                'stock' => (float) $product->current_stock,
                'unit' => $product->unit->symbol,
            ])->values(),
            'serviceOptions' => $servicePrices->map(fn (ServicePrice $price): array => [
                'id' => (string) $price->id,
                'label' => $price->serviceType->name.' - '.$price->paperType->code.' - '.($price->printMode?->name ?? $price->side_mode->label()).' — Rp '.number_format((float) $price->price, 0, ',', '.'),
                'price' => (float) $price->price,
                'side_mode' => $price->side_mode->value,
            ])->values(),
        ]);
    }

    public function store(StoreDraftOrderRequest $request, CreateDraftOrderAction $action): RedirectResponse
    {
        $this->authorize('orders.create');
        $order = $action->execute($request->user(), $request->validated());

        return to_route('orders.show', $order)->with('status', "Draft {$order->order_number} berhasil dibuat.");
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('orders.view');

        $order->load([
            'customer',
            'channel',
            'creator',
            'items.product.unit',
            'items.servicePrice.serviceType',
            'items.servicePrice.paperType',
            'items.servicePrice.printMode',
            'items.serviceDetail.paperType',
            'items.serviceDetail.paperType.inventoryProduct',
            'statusHistories.changedBy',
            'payments.paymentMethod',
            'payments.receiver',
            'adjustments.creator',
        ]);

        $availableStatuses = collect(OrderStatus::cases())
            ->filter(fn (OrderStatus $status): bool => $order->status->canTransitionTo($status))
            ->filter(fn (OrderStatus $status): bool => $status === OrderStatus::Cancelled
                ? $request->user()->can('orders.cancel')
                : $request->user()->can('orders.update'));

        $paymentMethods = PaymentMethod::query()->where('is_active', true)->orderBy('name')->get();

        return view('orders.show', compact('order', 'availableStatuses', 'paymentMethods'));
    }

    public function updateStatus(
        ChangeOrderStatusRequest $request,
        Order $order,
        ChangeOrderStatusAction $action
    ): RedirectResponse {
        $validated = $request->validated();
        $targetStatus = OrderStatus::from($validated['status']);

        $action->execute(
            $order,
            $request->user(),
            $targetStatus,
            $validated['reason'] ?? null,
            $validated['consumed_sheets'] ?? []
        );

        return to_route('orders.show', $order)
            ->with('status', "Status pesanan berhasil diubah menjadi {$targetStatus->label()}.");
    }
}
