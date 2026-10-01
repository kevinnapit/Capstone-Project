<?php

namespace App\Actions\Orders;

use App\Enums\OrderItemType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\Product;
use App\Models\ServicePrice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\OrderNumberGenerator;
use App\Services\OrderPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateDraftOrderAction
{
    public function __construct(
        private readonly OrderPricingService $pricing,
        private readonly OrderNumberGenerator $numberGenerator,
        private readonly AuditLogger $audit,
    ) {}

    public function execute(User $creator, array $data): Order
    {
        $items = $data['items'] ?? [];
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages(['items' => 'Pesanan minimal memiliki satu item.']);
        }

        return DB::transaction(function () use ($creator, $data, $items): Order {
            $channel = OrderChannel::query()->find($data['channel_id'] ?? null);
            if (! $channel) {
                throw ValidationException::withMessages(['channel_id' => 'Channel pesanan tidak valid.']);
            }

            $customerId = $data['customer_id'] ?? null;
            $newCustomer = $data['customer'] ?? [];

            if ($customerId !== null && ! empty($newCustomer['name'])) {
                throw ValidationException::withMessages(['customer' => 'Pilih pelanggan lama atau buat pelanggan baru, bukan keduanya.']);
            }

            if ($customerId !== null && ! Customer::query()->whereKey($customerId)->exists()) {
                throw ValidationException::withMessages(['customer_id' => 'Pelanggan tidak valid.']);
            }

            if ($customerId === null && ! empty($newCustomer['name'])) {
                $customerId = Customer::query()->create([
                    'name' => $newCustomer['name'],
                    'phone' => $newCustomer['phone'] ?? null,
                    'address' => $newCustomer['address'] ?? null,
                ])->id;
            }

            $orderedAt = now();
            $order = Order::query()->create([
                'order_number' => $this->numberGenerator->generate($orderedAt),
                'customer_id' => $customerId,
                'channel_id' => $channel->id,
                'created_by' => $creator->id,
                'status' => OrderStatus::Draft,
                'ordered_at' => $orderedAt,
                'subtotal' => 0,
                'discount_amount' => 0,
                'grand_total' => 0,
                'paid_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::Draft,
                'changed_by' => $creator->id,
                'reason' => 'Pesanan dibuat sebagai draft.',
            ]);

            $subtotals = [];
            foreach ($items as $index => $input) {
                $line = $this->makeLine($input, $index);
                $item = $order->items()->create([
                    ...$line['item'],
                    'notes' => $input['notes'] ?? null,
                ]);

                if ($line['service_detail']) {
                    $item->serviceDetail()->create($line['service_detail']);
                }

                $subtotals[] = $line['item']['subtotal'];
            }

            $subtotal = $this->pricing->sum($subtotals);
            $order->update(['subtotal' => $subtotal, 'grand_total' => $subtotal]);

            $this->audit->log($creator, 'order.created', $order, null, [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'grand_total' => $subtotal,
            ], "Draft {$order->order_number} dibuat.");

            return $order->fresh(['customer', 'channel', 'creator', 'items.product', 'items.servicePrice', 'items.serviceDetail', 'statusHistories']);
        });
    }

    private function makeLine(mixed $input, int $index): array
    {
        if (! is_array($input)) {
            throw ValidationException::withMessages(["items.{$index}" => 'Format item tidak valid.']);
        }

        $type = OrderItemType::tryFrom((string) ($input['type'] ?? ''));

        if ($type === OrderItemType::Product) {
            $product = Product::query()->lockForUpdate()->find($input['product_id'] ?? null);
            if (! $product) {
                throw ValidationException::withMessages(["items.{$index}.product_id" => 'Produk tidak valid.']);
            }

            return $this->pricing->productLine($product, $input['quantity'] ?? null);
        }

        if ($type === OrderItemType::Service) {
            $servicePrice = ServicePrice::query()->lockForUpdate()->find($input['service_price_id'] ?? null);
            if (! $servicePrice) {
                throw ValidationException::withMessages(["items.{$index}.service_price_id" => 'Tarif layanan tidak valid.']);
            }

            return $this->pricing->serviceLine($servicePrice, $input['pages'] ?? null, $input['copies'] ?? null);
        }

        throw ValidationException::withMessages(["items.{$index}.type" => 'Tipe item harus product atau service.']);
    }
}
