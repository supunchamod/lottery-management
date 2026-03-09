<?php

namespace App\Http\Controllers;

use App\Models\BoardTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardTransactionController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Ledger (index)
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $from   = $request->input('from');
        $to     = $request->input('to');
        $type   = $request->input('type');         // get_tickets | paid_bill | credit
        $period = $request->input('period', '');   // week | month | ''

        // Date-range shortcuts
        if ($period === 'week') {
            $from = now()->startOfWeek()->toDateString();
            $to   = now()->endOfWeek()->toDateString();
        } elseif ($period === 'month') {
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        }

        // ── Filtered ledger rows ─────────────────────────────────────────────
        $query = BoardTransaction::orderBy('date')->orderBy('id');

        if ($from) $query->whereDate('date', '>=', $from);
        if ($to)   $query->whereDate('date', '<=', $to);
        if ($type) $query->where('description', $type);

        $transactions = $query->paginate(50)->withQueryString();

        // ── Summary cards (always current month) ─────────────────────────────
        $thisMonth    = now();
        $monthQuery   = BoardTransaction::forMonth($thisMonth->year, $thisMonth->month);

        $monthReceived = (clone $monthQuery)
            ->where('description', 'get_tickets')
            ->sum('ticket_value');

        $monthPaid = (clone $monthQuery)
            ->where('description', 'paid_bill')
            ->selectRaw('SUM(winning_amount + cash_amount + bank_deposits) as total')
            ->value('total') ?? 0;

        // Outstanding balance = last recorded running balance overall
        $outstanding = BoardTransaction::orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance') ?? 0;

        // ── Total tickets this month ─────────────────────────────────────────
        $monthTicketQty = (clone $monthQuery)
            ->where('description', 'get_tickets')
            ->sum('ticket_qty');

        return view('board-transactions.index', compact(
            'transactions', 'from', 'to', 'type', 'period',
            'monthReceived', 'monthPaid', 'outstanding', 'monthTicketQty'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create form
    // ─────────────────────────────────────────────────────────────────────────

    public function create(): View
    {
        $defaultType = 'get_tickets';
        return view('board-transactions.create', [
            'defaultType' => $defaultType,
            'nlbTiers'    => BoardTransaction::NLB_TIERS,
            'dlbTiers'    => BoardTransaction::DLB_TIERS,
            'cashDenoms'  => BoardTransaction::CASH_DENOMS,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTransaction($request);

        $tx = new BoardTransaction($data);

        // Build JSON breakdowns for paid_bill
        if ($data['description'] === 'paid_bill') {
            $tx->nlb_tiers  = $this->extractTiers($request, array_keys(BoardTransaction::NLB_TIERS));
            $tx->dlb_tiers  = $this->extractTiers($request, array_keys(BoardTransaction::DLB_TIERS));
            $tx->cash_denoms = $this->extractTiers($request, array_keys(BoardTransaction::CASH_DENOMS));

            // Compute totals from denomination inputs
            $tx->winning_amount = $this->sumTiers($tx->nlb_tiers, BoardTransaction::NLB_TIERS)
                                + $this->sumTiers($tx->dlb_tiers, BoardTransaction::DLB_TIERS);

            $tx->cash_amount = $this->sumTiers($tx->cash_denoms, BoardTransaction::CASH_DENOMS);
        }

        $tx->computeCrAmount();
        $tx->save();

        // Rebuild running balances for all rows from this date onward
        BoardTransaction::recalculateBalances();

        return redirect()
            ->route('board-transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(BoardTransaction $boardTransaction): RedirectResponse
    {
        $boardTransaction->delete();
        BoardTransaction::recalculateBalances();

        return back()->with('success', 'Transaction deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function validateTransaction(Request $request): array
    {
        $base = $request->validate([
            'date'           => ['required', 'date'],
            'date_02'        => ['nullable', 'date'],
            'description'    => ['required', 'in:get_tickets,paid_bill,credit'],
            'ticket_qty'     => ['nullable', 'integer', 'min:0'],
            'ticket_value'   => ['nullable', 'numeric', 'min:0'],
            'bank_deposits'  => ['nullable', 'numeric', 'min:0'],
            'credit_amount'  => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        return array_merge([
            'ticket_value'  => 0,
            'bank_deposits' => 0,
            'credit_amount' => 0,
        ], $base);
    }

    /**
     * Pull denomination key => qty values from the request.
     * Returns only keys where qty > 0 to keep JSON compact.
     */
    private function extractTiers(Request $request, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $qty = (int) $request->input($key, 0);
            if ($qty > 0) {
                $result[$key] = $qty;
            }
        }
        return $result;
    }

    /**
     * Sum tier values: qty × denomination for each entry.
     */
    private function sumTiers(array $tiers, array $denomMap): float
    {
        $total = 0;
        foreach ($tiers as $col => $qty) {
            $total += ((int) $qty) * ($denomMap[$col] ?? 0);
        }
        return $total;
    }
}
