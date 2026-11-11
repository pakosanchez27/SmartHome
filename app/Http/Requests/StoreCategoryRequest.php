<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Services\CurrentHousehold;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * EP04-08: alta de categoría de ingreso o gasto del hogar.
 */
class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $householdId = (int) app(CurrentHousehold::class)->resolve($this)?->id;

        return [
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('categories', 'name')
                    ->where('household_id', $householdId)
                    ->where('kind', $this->input('kind')),
            ],
            'kind' => ['required', 'string', Rule::in(TransactionType::categoryKinds())],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe una categoría con ese nombre y tipo en tu hogar.',
            'kind.in' => 'Una categoría solo puede ser de ingreso o de gasto.',
        ];
    }
}
