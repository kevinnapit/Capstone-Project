<?php

namespace App\Http\Requests;

use App\Enums\SideMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'print_mode_id' => $this->input('print_mode_id') ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'paper_type_id' => ['required', 'integer', 'exists:paper_types,id'],
            'print_mode_id' => ['nullable', 'integer', 'exists:print_modes,id'],
            'side_mode' => ['required', Rule::enum(SideMode::class)],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
