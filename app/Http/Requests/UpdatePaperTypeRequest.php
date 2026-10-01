<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaperTypeRequest extends FormRequest
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
        $paperType = $this->route('paper_type');

        return [
            'inventory_product_id' => ['required', 'integer', 'exists:products,id', Rule::unique('paper_types')->ignore($paperType)],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('paper_types')->ignore($paperType)],
            'name' => ['required', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
