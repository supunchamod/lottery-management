<?php

namespace App\Http\Controllers;

use App\Models\AssistantRoute;
use App\Models\BundleLog;
use App\Models\Cheque;
use App\Models\DailySale;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LotteryStock;
use App\Models\SalesAssistant;
use App\Models\Winning;
use App\Services\LedgerService;
use Illuminate\Http\Request;

/**
 * Thin controller that serves the Blade views for all remaining nav pages.
 * Complex store/update logic lives in the dedicated API controllers.
 */
class PageController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    // ── Daily Sales ──────────────────────────────────────────────────────────

    public function dailySalesIndex(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $sales = DailySale::with('assistant')
            ->whereDate('date', $date)
            ->orderBy('id')
            ->get();

        $dayTotals = [
            'issued'  => $sales->sum('tickets_issued_val'),
            'returns' => $sales->sum('returns_val'),
            'winning' => $sales->sum('winning_val'),
            'cash'    => $sales->sum('cash_collected'),
            'balance' => $sales->sum('balance'),
        ];

        return view('daily-sales.index', [
            'sales'      => $sales,
            'dayTotals'  => $dayTotals,
            'assistants' => SalesAssistant::orderBy('created_at', 'asc')->get(),
        ]);
    }

    public function dailySalesStore(Request $request)
    {
        $data = $request->validate([
            'assistant_id'       => ['required', 'exists:sales_assistants,id'],
            'date'               => ['required', 'date'],
            'tickets_issued_val' => ['required', 'numeric', 'min:0'],
            'returns_qty'        => ['required', 'integer', 'min:0'],
            'returns_val'        => ['required', 'numeric', 'min:0'],
            'winning_val'        => ['required', 'numeric', 'min:0'],
            'cash_collected'     => ['required', 'numeric', 'min:0'],
        ]);

        $assistant = SalesAssistant::findOrFail($data['assistant_id']);
        $sale      = new DailySale($data);
        $sale->computeBalance();
        $sale->save();

        $this->ledger->postDailySale($assistant, $sale);

        return redirect()->route('daily-sales.index', ['date' => $data['date']])
            ->with('success', "Record saved for {$assistant->name}.");
    }

    // ── Winnings ─────────────────────────────────────────────────────────────

    public function winningsIndex()
    {
        return view('winnings.index', [
            'winnings' => Winning::orderByDesc('date')->paginate(20),
        ]);
    }

    public function winningsCreate(Request $request)
    {
        $date     = $request->input('date', now()->toDateString());
        $existing = Winning::where('date', $date)->first();

        return view('winnings.create', compact('date', 'existing'));
    }

    public function winningsStore(Request $request)
    {
        $data = $request->validate([
            'date'     => ['required', 'date'],
            ...$this->winningTierRules(),
        ]);

        $w = Winning::firstOrNew(['date' => $data['date']]);
        $w->fill($data);
        $w->computeTotals();
        $w->save();

        return redirect()->route('winnings.index')
            ->with('success', 'Winning record saved for ' . \Carbon\Carbon::parse($data['date'])->format('d M Y') . '.');
    }

    // ── Expenses ─────────────────────────────────────────────────────────────

    public function expensesIndex(Request $request)
    {
        $query = Expense::with('category')->orderByDesc('date')->orderByDesc('id');

        $query->when($request->filled('search'), fn ($q) =>
            $q->where('description', 'like', '%' . $request->search . '%')
        );

        $query->when($request->filled('date_from'), fn ($q) =>
            $q->whereDate('date', '>=', $request->date_from)
        );

        $query->when($request->filled('date_to'), fn ($q) =>
            $q->whereDate('date', '<=', $request->date_to)
        );

        $query->when($request->filled('category_id'), fn ($q) =>
            $q->where('category_id', $request->category_id)
        );

        $query->when($request->filled('amount_min'), fn ($q) =>
            $q->where('amount', '>=', $request->amount_min)
        );

        $query->when($request->filled('amount_max'), fn ($q) =>
            $q->where('amount', '<=', $request->amount_max)
        );

        return view('expenses.index', [
            'expenses'   => $query->paginate(30)->withQueryString(),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'filters'    => $request->only(['search', 'date_from', 'date_to', 'category_id', 'amount_min', 'amount_max']),
            'todayTotal' => Expense::whereDate('date', now())->sum('amount'),
            'monthTotal' => Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'monthCount' => Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ]);
    }

    public function expensesStore(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'date'        => ['required', 'date'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function expensesUpdate(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'date'        => ['required', 'date'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function expensesDestroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    // ── Cheques ───────────────────────────────────────────────────────────────

    public function chequesIndex()
    {
        return view('cheques.index', [
            'cheques'      => Cheque::orderByDesc('due_date')->get(),
            'pendingTotal' => Cheque::pending()->sum('amount'),
            'clearedTotal' => Cheque::cleared()->whereMonth('due_date', now()->month)->sum('amount'),
            'overdueCount' => Cheque::overdue()->count(),
        ]);
    }

    public function chequesCreate()
    {
        return view('cheques.create');
    }

    public function chequesStore(Request $request)
    {
        $data = $request->validate([
            'bank_name' => 'required|string|max:255',
            'cheque_no' => 'required|string|max:100',
            'amount'    => 'required|numeric|min:0',
            'due_date'  => 'required|date',
            'status'    => 'in:pending,cleared',
        ]);

        Cheque::create($data);

        return redirect()->route('cheques.index')->with('success', 'Cheque added successfully.');
    }

    public function chequesClear(Cheque $cheque)
    {
        $cheque->update(['status' => 'cleared']);
        return back()->with('success', "Cheque #{$cheque->cheque_no} marked as cleared.");
    }

    // ── Assistants ────────────────────────────────────────────────────────────

    public function assistantsIndex()
    {
        $assistants = SalesAssistant::with('route')
            ->withCount('dailySales')
            ->orderBy('created_at', 'asc')
            ->orderBy('sales_assistants.created_at', 'asc')
            ->get();

        $grouped = $assistants
            ->groupBy(fn ($a) => $a->route?->name ?? 'Unassigned')
            ->sortBy(fn ($group, $key) => $key === 'Unassigned' ? 'ZZZZZ' : $key);

        return view('assistants.index', [
            'assistants' => $assistants,
            'grouped'    => $grouped,
        ]);
    }

    public function assistantsCreate()
    {
        return view('assistants.create', [
            'routes' => AssistantRoute::orderBy('name')->get(),
        ]);
    }

    public function assistantsStore(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:20'],
            'address'  => ['required', 'string'],
            'route_id' => ['nullable', 'exists:assistant_routes,id'],
        ]);

        SalesAssistant::create($data);

        return redirect()->route('assistants.index')->with('success', 'Assistant added.');
    }

    public function assistantsEdit(SalesAssistant $assistant)
    {
        return view('assistants.edit', [
            'assistant' => $assistant,
            'routes'    => AssistantRoute::orderBy('name')->get(),
        ]);
    }

    public function assistantsUpdate(Request $request, SalesAssistant $assistant)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:20'],
            'address'  => ['required', 'string'],
            'route_id' => ['nullable', 'exists:assistant_routes,id'],
        ]);

        $assistant->update($data);

        return redirect()->route('assistants.index')->with('success', "{$assistant->name} updated.");
    }

    public function assistantsLedger(SalesAssistant $assistant)
    {
        $entries = $assistant->ledgers()->orderByDesc('date')->orderByDesc('id')->paginate(30);
        return view('assistants.ledger', compact('assistant', 'entries'));
    }

    public function assistantsDestroy(SalesAssistant $assistant)
    {
        // Check if the assistant has ledger entries or sales before deleting 
        // to prevent integrity issues, or simply delete if your database allows cascade.
        $assistant->delete();

        return redirect()->route('assistants.index')
            ->with('success', "Assistant '{$assistant->name}' has been deleted.");
    }

    // ── Lotteries ─────────────────────────────────────────────────────────────

    public function lotteriesIndex()
    {
        return view('lotteries.index', [
            'lotteries' => \App\Models\Lottery::orderBy('created_at', 'asc')->get(),
        ]);
    }

    public function lotteriesCreate()
    {
        return view('lotteries.create');
    }

    public function lotteriesStore(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'board'      => ['required', 'in:NLB,DLB'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);

        \App\Models\Lottery::create($data);

        return redirect()->route('lotteries.index')->with('success', "{$data['name']} added.");
    }

    public function lotteriesEdit(\App\Models\Lottery $lottery)
    {
        return view('lotteries.edit', compact('lottery'));
    }

    public function lotteriesUpdate(Request $request, \App\Models\Lottery $lottery)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'board'      => ['required', 'in:NLB,DLB'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $lottery->update($data);

        return redirect()->route('lotteries.index')->with('success', "{$lottery->name} updated.");
    }

    public function lotteriesDestroy(\App\Models\Lottery $lottery)
    {
        $hasStocks    = $lottery->stocks()->exists();
        $hasTickets   = \App\Models\TicketDistribution::where('lottery_id', $lottery->id)->exists();
        $hasDailySales = \App\Models\DailyTicketStock::where('lottery_id', $lottery->id)->exists();

        if ($hasStocks || $hasTickets || $hasDailySales) {
            return redirect()->route('lotteries.index')
                ->with('error', "Cannot delete \"{$lottery->name}\" — it has existing stock or sales history.");
        }

        $name = $lottery->name;
        $lottery->delete();

        return redirect()->route('lotteries.index')->with('success', "\"{$name}\" deleted successfully.");
    }

    // ── Stock ─────────────────────────────────────────────────────────────────

    public function stockIndex()
    {
        return view('stock.index', [
            'stocks' => LotteryStock::with(['agent', 'lottery'])->orderByDesc('date')->paginate(30),
        ]);
    }

    public function stockCreate()
    {
        return view('stock.create', [
            'assistants' => SalesAssistant::orderBy('created_at', 'asc')->get(),
            'lotteries'  => \App\Models\Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get(),
        ]);
    }

    public function stockStore(Request $request)
    {
        $data = $request->validate([
            'date'       => ['required', 'date'],
            'agent_id'   => ['required', 'exists:sales_assistants,id'],
            'lottery_id' => ['required', 'exists:lotteries,id'],
            'qty_issued' => ['required', 'integer', 'min:1'],
        ]);

        LotteryStock::create($data);

        return redirect()->route('stock.index')->with('success', 'Stock issued successfully.');
    }

    // ── Bundle Counter ────────────────────────────────────────────────────────

    public function bundleCounterIndex()
    {
        $logs = BundleLog::with('savedBy')
            ->orderByDesc('session_date')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('bundle-counter.index', compact('logs'));
    }

    public function bundleCounterStore(Request $request)
    {
        $data = $request->validate([
            'session_date'     => ['required', 'date'],
            'total_bundles'    => ['required', 'integer', 'min:0'],
            'total_tickets'    => ['required', 'integer', 'min:0'],
            'scanned_barcodes' => ['nullable', 'array'],
            'scanned_barcodes.*' => ['string'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $data['saved_by'] = auth()->id();

        BundleLog::create($data);

        return response()->json(['message' => 'Session saved successfully.']);
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reportsIndex(Request $request)
    {
        $data = ['summary' => null];

        if ($request->filled('from') && $request->filled('to')) {
            $data['summary'] = app(DailySummaryController::class)->range($request)->getData(true);
        }

        return view('reports.index', $data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function winningTierRules(): array
    {
        $rules = [];
        foreach (array_keys(\App\Models\Winning::NLB_TIERS) as $col) {
            $rules[$col] = ['nullable', 'integer', 'min:0'];
        }
        foreach (array_keys(\App\Models\Winning::DLB_TIERS) as $col) {
            $rules[$col] = ['nullable', 'integer', 'min:0'];
        }
        return $rules;
    }
}
