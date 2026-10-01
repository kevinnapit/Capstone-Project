<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => strtoupper(trim((string) $this->sku)),
            'name' => trim((string) $this->name),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'sku' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'selling_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
