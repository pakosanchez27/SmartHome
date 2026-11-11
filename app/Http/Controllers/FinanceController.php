<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesHousehold;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * EP-04 · C01: panorama financiero del hogar.
 *
 * Muestra saldos reconstruidos, disponible real y últimos movimientos.
 * Nunca presenta un límite de crédito como efectivo disponible (RG-02/RG-08).
 */
class FinanceController extends Controller
{
    use ResolvesHousehold;

    public function __construct(private readonly LedgerService $ledger) {}

    public function index(Request $request): View
    {
        $household = $this->household($request);
        $this->authorize('viewAny', [Account::class, $household]);

        $viewer = $request->user();
        $viewerRole = $viewer->roleIn($household);

        $accounts = Account::query()
            ->visibleTo($viewer, $household)
            ->orderByRaw("CASE WHEN type = 'credit' THEN 1 ELSE 0 END")
            ->orderBy('name')
            ->get();

        $balances = $this->ledger->balancesFor($accounts);

        $cashAvailable = 0;
        $creditOwed = 0;
        $availableByAccount = [];

        foreach ($accounts as $account) {
            $balance = $balances[$account->id];
            $availableByAccount[$account->id] = $this->ledger->available($account, $balance);

            if ($account->isCredit()) {
                $creditOwed += $this->ledger->owed($account, $balance);
            } else {
                $cashAvailable += $this->ledger->available($account, $balance);
            }
        }

        $from = Carbon::today()->startOfMonth();
        $to = Carbon::today();
        $totals = $this->ledger->periodTotals($household, $viewer, $from, $to);

        $recent = Transaction::query()
            ->visibleTo($viewer, $household)
            ->with(['lines.account', 'category'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('finance.index', [
            'household' => $household,
            'viewerRole' => $viewerRole,
            'accounts' => $accounts,
            'balances' => $balances,
            'availableByAccount' => $availableByAccount,
            'cashAvailable' => $cashAvailable,
            'creditOwed' => $creditOwed,
            'totals' => $totals,
            'recent' => $recent,
            'periodFrom' => $from,
            'periodTo' => $to,
        ]);
    }
}
