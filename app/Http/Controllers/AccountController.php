<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Http\Controllers\Concerns\ResolvesHousehold;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use App\Services\LedgerService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * EP-04 · C04: cuentas del hogar y movimientos de una cuenta.
 *
 * El saldo siempre se calcula; nunca se acepta del formulario (RG-02).
 */
class AccountController extends Controller
{
    use ResolvesHousehold;

    public function __construct(
        private readonly LedgerService $ledger,
        private readonly ActivityLogger $logger,
    ) {}

    public function create(Request $request): View
    {
        $household = $this->household($request);
        $this->authorize('create', [Account::class, $household]);

        return view('finance.accounts.form', [
            'household' => $household,
            'account' => null,
            'types' => AccountType::cases(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $household = $this->household($request);
        $this->authorize('create', [Account::class, $household]);

        $validated = $request->validated();
        $isPrivate = $validated['visibility'] === 'private';

        $account = DB::transaction(function () use ($request, $household, $validated, $isPrivate) {
            $account = Account::create([
                'household_id' => $household->id,
                'owner_user_id' => $isPrivate ? $request->user()->id : null,
                'name' => $validated['name'],
                'type' => AccountType::from($validated['type']),
                'currency' => $household->currency ?? 'MXN',
                'opening_balance' => Money::parse($validated['opening_balance']),
                'credit_limit' => isset($validated['credit_limit']) ? Money::parse($validated['credit_limit']) : null,
                'is_active' => true,
            ]);

            $this->logger->log($household, $request->user(), ActivityLog::CREATED_ACCOUNT, $account, [
                'type' => $validated['type'],
                'visibility' => $validated['visibility'],
            ]);

            return $account;
        });

        return redirect()
            ->route('finance.accounts.show', $account)
            ->with('status', "La cuenta «{$account->name}» quedó lista con su saldo inicial.");
    }

    public function show(Request $request, Account $account): View
    {
        $this->authorize('view', $account);
        $household = $this->household($request);

        $filters = $request->validate([
            'type' => ['nullable', 'string', Rule::in(TransactionType::values())],
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = isset($filters['from']) ? Carbon::parse($filters['from']) : Carbon::today()->startOfMonth();
        $to = isset($filters['to']) ? Carbon::parse($filters['to']) : Carbon::today();

        $transactions = Transaction::query()
            ->whereHas('lines', fn ($query) => $query->where('account_id', $account->id))
            ->visibleTo($request->user(), $household)
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->ofType(TransactionType::from($type)))
            ->when($filters['category_id'] ?? null, fn ($query, $category) => $query->where('category_id', (int) $category))
            ->occurredBetween($from, $to)
            ->with(['lines.account', 'category'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $balance = $this->ledger->balance($account);

        return view('finance.accounts.show', [
            'household' => $household,
            'account' => $account,
            'balance' => $balance,
            'available' => $this->ledger->available($account, $balance),
            'owed' => $this->ledger->owed($account, $balance),
            'transactions' => $transactions,
            'categories' => Category::query()->where('household_id', $household->id)->active()->orderBy('name')->get(),
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'canOperate' => in_array($request->user()->roleIn($household), ['admin', 'member'], true),
        ]);
    }

    public function edit(Request $request, Account $account): View
    {
        $this->authorize('update', $account);

        return view('finance.accounts.form', [
            'household' => $this->household($request),
            'account' => $account,
            'types' => AccountType::cases(),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $household = $account->household;
        $validated = $request->validated();
        $isPrivate = $validated['visibility'] === 'private';

        DB::transaction(function () use ($request, $account, $household, $validated, $isPrivate) {
            $account->update([
                'name' => $validated['name'],
                'type' => AccountType::from($validated['type']),
                'credit_limit' => isset($validated['credit_limit']) ? Money::parse($validated['credit_limit']) : null,
                'is_active' => (bool) $validated['is_active'],
                'owner_user_id' => $isPrivate ? $request->user()->id : null,
                'shared_at' => null,
            ]);

            $this->logger->log($household, $request->user(), ActivityLog::UPDATED_ACCOUNT, $account, [
                'type' => $validated['type'],
                'visibility' => $validated['visibility'],
                'active' => (bool) $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('finance.accounts.show', $account)
            ->with('status', 'Los datos de la cuenta quedaron actualizados.');
    }
}
