<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $status = OrderStatus::tryFrom((string) $this->input('status'));

        return match ($status) {
            OrderStatus::Cancelled => $this->user()?->can('orders.cancel') ?? false,
            null => $this->user()?->can('orders.update') || $this->user()?->can('orders.cancel'),
            default => $this->user()?->can('orders.update') ?? false,
        };
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:500', 'required_if:status,'.OrderStatus::Cancelled->value],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'Alasan pembatalan wajib diisi.',
        ];
    }
}
