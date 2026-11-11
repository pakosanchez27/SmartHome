<?php

namespace Database\Seeders;

use App\Actions\RecordTransaction;
use App\Actions\VoidTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * EP01-05/EP-04: datos ficticios del módulo financiero para demos locales.
 *
 * Reglas:
 * - Todo es inventado (nombres, correos example.com, importes); nunca datos
 *   reales del hogar (AGENTS.md §0.8).
 * - Los movimientos se registran con la acción de dominio para que los datos
 *   demo tengan el mismo historial y auditoría que los reales.
 * - La transferencia hacia el ahorro privado demuestra la regla de
 *   privacidad en servidor: el movimiento queda privado por sí solo.
 *
 * Usuarios demo (contraseña «password»): ana.rivera@example.com (admin),
 * luis.torres@example.com (integrante).
 */
class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Household::query()->where('name', 'Hogar Rivera (demo)')->exists()) {
            return;
        }

        $ana = User::create([
            'name' => 'Ana Rivera',
            'email' => 'ana.rivera@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $luis = User::create([
            'name' => 'Luis Torres',
            'email' => 'luis.torres@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $household = new Household([
            'name' => 'Hogar Rivera (demo)',
            'currency' => 'MXN',
            'timezone' => 'America/Mexico_City',
            'cutoff_day' => 15,
            'budget_carryover' => true,
        ]);
        $household->creator()->associate($ana);
        $household->save();

        $household->users()->attach($ana, ['role' => 'admin']);
        $household->users()->attach($luis, ['role' => 'member']);

        $categories = [];

        foreach (['Alimentación', 'Transporte', 'Servicios', 'Hogar', 'Salud', 'Entretenimiento'] as $name) {
            $categories[$name] = Category::create([
                'household_id' => $household->id,
                'name' => $name,
                'kind' => TransactionType::Expense,
                'is_active' => true,
            ]);
        }

        foreach (['Nómina', 'Ingresos extra'] as $name) {
            $categories[$name] = Category::create([
                'household_id' => $household->id,
                'name' => $name,
                'kind' => TransactionType::Income,
                'is_active' => true,
            ]);
        }

        $accounts = [
            'efectivo' => Account::create([
                'household_id' => $household->id,
                'name' => 'Efectivo',
                'type' => AccountType::Cash,
                'currency' => 'MXN',
                // $2,500: cubre súper ($1,450.75) y apartado ($500) con margen.
                'opening_balance' => 250000,
                'is_active' => true,
            ]),
            'nomina' => Account::create([
                'household_id' => $household->id,
                'name' => 'Cuenta de nómina',
                'type' => AccountType::Debit,
                'currency' => 'MXN',
                'opening_balance' => 820000,
                'is_active' => true,
            ]),
            'credito' => Account::create([
                'household_id' => $household->id,
                'name' => 'Tarjeta de crédito',
                'type' => AccountType::Credit,
                'currency' => 'MXN',
                'opening_balance' => -350000,
                'credit_limit' => 2500000,
                'is_active' => true,
            ]),
            // Ahorro personal de Ana: privada, invisible para Luis.
            'ahorro' => Account::create([
                'household_id' => $household->id,
                'owner_user_id' => $ana->id,
                'shared_at' => null,
                'name' => 'Ahorro de Ana',
                'type' => AccountType::Savings,
                'currency' => 'MXN',
                'opening_balance' => 600000,
                'is_active' => true,
            ]),
        ];

        $record = app(RecordTransaction::class);

        $record->handle($household, $ana, [
            'type' => TransactionType::Income,
            'amount_cents' => 950000,
            'occurred_on' => Carbon::today()->subDays(12)->toDateString(),
            'description' => 'Nómina quincenal',
            'account_id' => $accounts['nomina']->id,
            'category_id' => $categories['Nómina']->id,
            'visibility' => 'shared',
        ]);

        $record->handle($household, $ana, [
            'type' => TransactionType::Expense,
            'amount_cents' => 145075,
            'occurred_on' => Carbon::today()->subDays(10)->toDateString(),
            'description' => 'Súper de la semana',
            'notes' => 'Despensa del mercado y frutas.',
            'account_id' => $accounts['efectivo']->id,
            'category_id' => $categories['Alimentación']->id,
            'visibility' => 'shared',
        ]);

        $record->handle($household, $luis, [
            'type' => TransactionType::Expense,
            'amount_cents' => 60000,
            'occurred_on' => Carbon::today()->subDays(7)->toDateString(),
            'description' => 'Gasolina',
            'account_id' => $accounts['nomina']->id,
            'category_id' => $categories['Transporte']->id,
            'visibility' => 'shared',
        ]);

        $record->handle($household, $ana, [
            'type' => TransactionType::Expense,
            'amount_cents' => 89040,
            'occurred_on' => Carbon::today()->subDays(5)->toDateString(),
            'description' => 'Recibo de luz',
            'account_id' => $accounts['nomina']->id,
            'category_id' => $categories['Servicios']->id,
            'visibility' => 'shared',
        ]);

        // La cuenta de destino es privada: el servidor marca el movimiento
        // como privado aunque se pida «shared» (RG-01/ADR-0002).
        $record->handle($household, $ana, [
            'type' => TransactionType::Transfer,
            'amount_cents' => 50000,
            'occurred_on' => Carbon::today()->subDays(3)->toDateString(),
            'description' => 'Apartado semanal',
            'account_id' => $accounts['efectivo']->id,
            'destination_account_id' => $accounts['ahorro']->id,
            'category_id' => null,
            'visibility' => 'shared',
        ]);

        $record->handle($household, $luis, [
            'type' => TransactionType::Expense,
            'amount_cents' => 32150,
            'occurred_on' => Carbon::today()->subDays(2)->toDateString(),
            'description' => 'Farmacia',
            'account_id' => $accounts['credito']->id,
            'category_id' => $categories['Salud']->id,
            'visibility' => 'shared',
        ]);

        // Anulado: demuestra que el historial se conserva (RG-10).
        $duplicado = $record->handle($household, $luis, [
            'type' => TransactionType::Expense,
            'amount_cents' => 60000,
            'occurred_on' => Carbon::today()->subDays(1)->toDateString(),
            'description' => 'Cargo duplicado de gasolina',
            'account_id' => $accounts['nomina']->id,
            'category_id' => $categories['Transporte']->id,
            'visibility' => 'shared',
        ]);

        app(VoidTransaction::class)->handle($duplicado, $ana);
    }
}
