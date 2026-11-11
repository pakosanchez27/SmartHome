<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Rules\MoneyAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * EP04-02/EP04-07: edición de una cuenta existente.
 *
 * El saldo inicial no se edita aquí: cambiarlo reescribiría el histórico
 * (RG-02). Se ajusta con un movimiento, no con una edición silenciosa.
 */
class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $account = $this->route('account');
        $ownerId = $this->input('visibility') === 'private' ? $this->user()->id : null;

        return [
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('accounts', 'name')
                    ->where('household_id', $account->household_id)
                    ->where('owner_user_id', $ownerId)
                    ->ignore($account->id),
            ],
            'type' => ['required', 'string', Rule::in(AccountType::values())],
            'credit_limit' => ['nullable', 'string', 'max:20', new MoneyAmount(0)],
            'visibility' => ['required', 'string', Rule::in(['private', 'shared'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);

        if ($this->input('type') !== AccountType::Credit->value) {
            $this->merge(['credit_limit' => null]);
        }
    }
}
