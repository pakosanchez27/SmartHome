<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Rules\MoneyAmount;
use App\Services\CurrentHousehold;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * EP04-04: registro de ingreso, gasto o transferencia desde C05/C13.
 *
 * Valida en servidor que las cuentas y categorías pertenezcan al hogar activo,
 * sean visibles para quien registra y estén activas. Un ID ajeno o privado no
 * pasa esta barrera aunque venga en el formulario (RG-01).
 */
class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(TransactionType::values())],
            'amount' => ['required', 'string', 'max:20', new MoneyAmount(1)],
            'occurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'account_id' => ['required', 'integer'],
            'destination_account_id' => ['nullable', 'integer', 'required_if:type,transfer', 'different:account_id'],
            'category_id' => ['nullable', 'integer', 'required_if:type,income,expense'],
            'visibility' => ['required', 'string', Rule::in(['private', 'shared'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.required' => 'Elige el tipo de movimiento.',
            'amount.required' => 'Captura el importe.',
            'occurred_on.before_or_equal' => 'La fecha no puede ser futura: lo planeado se registra como pendiente.',
            'description.required' => 'Escribe un concepto breve.',
            'account_id.required' => 'Elige la cuenta.',
            'destination_account_id.required_if' => 'Elige la cuenta de destino de la transferencia.',
            'destination_account_id.different' => 'El origen y el destino deben ser cuentas distintas.',
            'category_id.required_if' => 'Elige una categoría.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['occurred_on' => $this->input('occurred_on') ?: Carbon::today()->toDateString()]);
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $household = app(CurrentHousehold::class)->resolve($this);
                $type = TransactionType::from((string) $this->input('type'));

                $origin = $this->visibleAccount((int) $this->input('account_id'), $household);

                if ($origin === null) {
                    $validator->errors()->add('account_id', 'Esa cuenta no está disponible en tu hogar.');

                    return;
                }

                if ($type->isTransfer()) {
                    $destination = $this->visibleAccount((int) $this->input('destination_account_id'), $household);

                    if ($destination === null) {
                        $validator->errors()->add('destination_account_id', 'Esa cuenta de destino no está disponible en tu hogar.');
                    }
                }

                if (! $type->isTransfer() && ! $this->validCategory($type)) {
                    $validator->errors()->add('category_id', 'Elige una categoría del tipo correcto.');
                }
            },
        ];
    }

    private function visibleAccount(int $accountId, mixed $household): ?Account
    {
        if ($household === null) {
            return null;
        }

        $account = Account::query()->where('household_id', $household->id)->find($accountId);

        if ($account === null || ! $account->is_active || ! $account->isVisibleTo($this->user())) {
            return null;
        }

        return $account;
    }

    private function validCategory(TransactionType $type): bool
    {
        $household = app(CurrentHousehold::class)->resolve($this);
        $categoryId = $this->input('category_id');

        if ($household === null || ! $categoryId) {
            return false;
        }

        return Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->ofKind($type)
            ->whereKey((int) $categoryId)
            ->exists();
    }
}
