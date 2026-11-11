<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\CurrentHousehold;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * EP04-07: corrección de datos descriptivos de un movimiento confirmado.
 * El importe y las cuentas no se editan: se anula y se vuelve a registrar.
 */
class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Si la petición no trae categoría, se conserva la actual del movimiento:
     * corregir otros datos no debe obligar a reelegir lo ya guardado.
     */
    protected function prepareForValidation(): void
    {
        /** @var Transaction|null $transaction */
        $transaction = $this->route('transaction');

        if ($transaction !== null && ! $transaction->type->isTransfer() && ! $this->filled('category_id')) {
            $this->merge(['category_id' => $transaction->category_id]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'occurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer'],
            'visibility' => ['required', 'string', Rule::in(['private', 'shared'])],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Transaction $transaction */
                $transaction = $this->route('transaction');

                if ($transaction->type->isTransfer()) {
                    return;
                }

                $household = app(CurrentHousehold::class)->resolve($this);
                $categoryId = $this->input('category_id');

                $valid = $household !== null
                    && $categoryId
                    && Category::query()
                        ->where('household_id', $household->id)
                        ->where('is_active', true)
                        ->ofKind($transaction->type)
                        ->whereKey((int) $categoryId)
                        ->exists();

                if (! $valid) {
                    $validator->errors()->add('category_id', 'Elige una categoría del tipo correcto.');
                }
            },
        ];
    }
}
