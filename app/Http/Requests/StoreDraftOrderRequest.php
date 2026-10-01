<?php

namespace App\Http\Requests;

use App\Enums\OrderItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDraftOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.create') ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'channel_id' => ['required', 'integer', 'exists:order_channels,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string', 'max:150', 'required_with:customer.phone,customer.address'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'customer.address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
        ];

        foreach ($this->input('items', []) as $index => $item) {
            $type = $item['type'] ?? null;
            $rules["items.{$index}.type"] = ['required', Rule::enum(OrderItemType::class)];
            $rules["items.{$index}.notes"] = ['nullable', 'string', 'max:500'];

            if ($type === OrderItemType::Product->value) {
                $rules["items.{$index}.product_id"] = ['required', 'integer', 'exists:products,id'];
                $rules["items.{$index}.quantity"] = ['required', 'numeric', 'gt:0', 'decimal:0,2'];
            } elseif ($type === OrderItemType::Service->value) {
                $rules["items.{$index}.service_price_id"] = ['required', 'integer', 'exists:service_prices,id'];
                $rules["items.{$index}.pages"] = ['required', 'integer', 'min:1'];
                $rules["items.{$index}.copies"] = ['required', 'integer', 'min:1'];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->filled('customer_id') && $this->filled('customer.name')) {
                    $validator->errors()->add('customer.name', 'Pilih pelanggan lama atau isi pelanggan baru, bukan keduanya.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Pesanan minimal memiliki satu item.',
            'items.min' => 'Pesanan minimal memiliki satu item.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.service_price_id.required' => 'Tarif layanan wajib dipilih.',
            'items.*.quantity.gt' => 'Kuantitas produk harus lebih besar dari nol.',
            'items.*.pages.min' => 'Jumlah halaman minimal satu.',
            'items.*.copies.min' => 'Jumlah copy minimal satu.',
        ];
    }
}
