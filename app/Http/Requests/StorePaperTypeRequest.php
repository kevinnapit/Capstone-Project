<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaperTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->code)), 'is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'inventory_product_id' => ['required', 'integer', 'exists:products,id', 'unique:paper_types,inventory_product_id'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:paper_types,code'],
            'name' => ['required', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
