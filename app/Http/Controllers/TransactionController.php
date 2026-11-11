<?php

namespace App\Http\Controllers;

use App\Actions\RecordTransaction;
use App\Actions\UpdateTransaction;
use App\Actions\VoidTransaction;
use App\Enums\TransactionType;
use App\Http\Controllers\Concerns\ResolvesHousehold;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LedgerService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * EP-04 · C05/C06/C13: registro, detalle y anulación de movimientos.
 *
 * Los formularios nunca envían saldos ni IDs fuera del alcance del usuario:
 * el servidor recalcula el dinero (RG-02) y filtra por hogar y visibilidad (RG-01).
 */
class TransactionController extends Controller
{
    use ResolvesHousehold;

    public function __construct(private readonly LedgerService $ledger) {}

    public function index(Request $request): View
    {
        $household = $this->household($request);
        $this->authorize('viewAny', [Transaction::class, $household]);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'type' => ['nullable', 'string', Rule::in(TransactionType::values())],
            'account_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = isset($filters['from']) ? Carbon::parse($filters['from']) : Carbon::today()->startOfMonth();
        $to = isset($filters['to']) ? Carbon::parse($filters['to']) : Carbon::today();

        $account = null;

        if (isset($filters['account_id'])) {
            // Un ID ajeno o privado responde 404: nunca se revela su existencia (RG-01).
            $account = Account::query()
                ->visibleTo($request->user(), $household)
                ->findOrFail((int) $filters['account_id']);
        }

        $transactions = $this->filteredQuery($request->user(), $household, $filters)
            ->with(['lines.account', 'category'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $activeFilters = collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->count();

        return view('finance.transactions.index', [
            'household' => $household,
            'transactions' => $transactions,
            'accounts' => $this->accountsFor($request->user(), $household),
            'categories' => $this->categoriesFor($household),
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'from' => $from,
            'to' => $to,
            'totals' => $this->ledger->periodTotals($household, $request->user(), $from, $to),
            'selectedAccount' => $account,
            'canOperate' => in_array($request->user()->roleIn($household), ['admin', 'member'], true),
        ]);
    }

    public function create(Request $request): View
    {
        $household = $this->household($request);
        $this->authorize('create', [Transaction::class, $household]);

        $type = TransactionType::tryFrom((string) $request->query('tipo')) ?? TransactionType::Expense;

        return view('finance.transactions.create', [
            'household' => $household,
            'accounts' => $this->accountsFor($request->user(), $household),
            'categories' => $this->categoriesFor($household),
            'defaultType' => $type,
        ]);
    }

    public function store(StoreTransactionRequest $request, RecordTransaction $record): RedirectResponse
    {
        $household = $this->household($request);
        $this->authorize('create', [Transaction::class, $household]);

        $validated = $request->validated();

        $transaction = $record->handle($household, $request->user(), [
            'type' => TransactionType::from($validated['type']),
            'amount_cents' => (int) Money::parse($validated['amount']),
            'occurred_on' => $validated['occurred_on'],
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'account_id' => (int) $validated['account_id'],
            'destination_account_id' => isset($validated['destination_account_id']) ? (int) $validated['destination_account_id'] : null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'visibility' => $validated['visibility'],
        ]);

        return redirect()
            ->route('finance.transactions.show', $transaction)
            ->with('status', 'El movimiento quedó registrado y el saldo se recalculó.');
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['lines.account', 'category', 'creator']);

        return view('finance.transactions.show', [
            'household' => $transaction->household,
            'transaction' => $transaction,
            'amount' => $transaction->amountCents(),
            'canEdit' => auth()->user()->can('update', $transaction),
        ]);
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('finance.transactions.edit', [
            'household' => $transaction->household,
            'transaction' => $transaction,
            'categories' => $this->categoriesFor($transaction->household, $transaction->type),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction, UpdateTransaction $update): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $update->handle($transaction, $request->user(), $request->validated());

        return redirect()
            ->route('finance.transactions.show', $transaction)
            ->with('status', 'Los datos del movimiento quedaron corregidos.');
    }

    public function annul(Transaction $transaction, VoidTransaction $void): RedirectResponse
    {
        $this->authorize('annul', $transaction);

        $void->handle($transaction, auth()->user());

        return redirect()
            ->route('finance.transactions.show', $transaction)
            ->with('status', 'El movimiento quedó anulado: sigue en el historial, pero ya no cuenta en los saldos.');
    }

    /** @param  array<string, mixed>  $filters */
    private function filteredQuery(User $viewer, Household $household, array $filters): Builder
    {
        return Transaction::query()
            ->visibleTo($viewer, $household)
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where('description', 'like', '%'.$term.'%');
            })
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->ofType(TransactionType::from($type)))
            ->when($filters['account_id'] ?? null, fn (Builder $query, $accountId) => $query->whereHas(
                'lines',
                fn (Builder $line) => $line->where('account_id', (int) $accountId)
            ))
            ->when($filters['category_id'] ?? null, fn (Builder $query, $categoryId) => $query->where('category_id', (int) $categoryId))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('occurred_on', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('occurred_on', '<=', $to));
    }

    /** @return Collection<int, Account> */
    private function accountsFor(User $viewer, Household $household)
    {
        return Account::query()
            ->visibleTo($viewer, $household)
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Category> */
    private function categoriesFor(Household $household, ?TransactionType $type = null)
    {
        return Category::query()
            ->where('household_id', $household->id)
            ->active()
            ->when($type && $type->isCategoryKind(), fn (Builder $query) => $query->ofKind($type))
            ->orderBy('name')
            ->get();
    }
}
