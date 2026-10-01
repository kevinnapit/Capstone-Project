<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
            'symbol' => strtolower(trim((string) $this->symbol)),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('units', 'symbol')->ignore($this->route('unit'))],
        ];
    }
}
