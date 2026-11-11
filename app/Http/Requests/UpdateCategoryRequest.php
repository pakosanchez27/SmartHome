<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * EP04-08: renombrar o desactivar una categoría sin romper su histórico.
 * El tipo (`kind`) no se cambia: los movimientos ya registrados dependen de él.
 */
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('categories', 'name')
                    ->where('household_id', $category->household_id)
                    ->where('kind', $category->kind->value)
                    ->ignore($category->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
