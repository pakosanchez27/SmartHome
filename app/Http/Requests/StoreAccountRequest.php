<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Rules\MoneyAmount;
use App\Services\CurrentHousehold;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * EP04-02: alta de cuenta. La moneda del hogar es MXN (config/smarthome.php)
 * y el saldo inicial se captura una sola vez; después solo se corrige con
 * movimientos (RG-02).
 */
class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $householdId = $this->householdId();
        $ownerId = $this->input('visibility') === 'private' ? $this->user()->id : null;

        return [
            'name' => [
                'required', 'string', 'max:80',
                // Dos personas pueden tener su propia cuenta «Efectivo»;
                // dentro del mismo alcance el nombre no debe repetirse.
                Rule::unique('accounts', 'name')
                    ->where('household_id', $householdId)
                    ->where('owner_user_id', $ownerId),
            ],
            'type' => ['required', 'string', Rule::in(AccountType::values())],
            'opening_balance' => ['required', 'string', 'max:20', new MoneyAmount],
            'credit_limit' => ['nullable', 'string', 'max:20', new MoneyAmount(0)],
            'visibility' => ['required', 'string', Rule::in(['private', 'shared'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Ponle un nombre a la cuenta.',
            'name.unique' => 'Ya tienes una cuenta con ese nombre en este alcance.',
            'type.required' => 'Elige el tipo de cuenta.',
            'opening_balance.required' => 'Indica el saldo inicial; si empieza en cero, escribe 0.',
            'visibility.required' => 'Decide si la cuenta es solo para ti o compartida con el hogar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // El límite de crédito solo aplica a tarjetas de crédito.
        if ($this->input('type') !== AccountType::Credit->value) {
            $this->merge(['credit_limit' => null]);
        }
    }

    private function householdId(): int
    {
        return (int) app(CurrentHousehold::class)->resolve($this)?->id;
    }
}
