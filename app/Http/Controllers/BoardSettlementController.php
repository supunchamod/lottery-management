<?php

namespace App\Http\Controllers;

use App\Models\BoardSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardSettlementController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Entry form + history on same page
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /board-settlement
     *
     * Shows the settlement entry form for today (or a requested date)
     * and the paginated history table below it.
     */
    public function index(Request $request): View
    {
        $date       = $request->input('date', today()->toDateString());
        $settlement = BoardSettlement::where('date', $date)->first();

        // ── History filters ────────────────────────────────────────────────
        $query = BoardSettlement::query()->orderByDesc('date');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }
        if ($request->filled('status')) {
            match ($request->input('status')) {
                'settled' => $query->where('balance', '<=', 0),
                'pending' => $query->where('balance',  '>',  0),
                default   => null,
            };
        }

        $history = $query->paginate(20)->withQueryString();

        // Running balance for display (cumulative, oldest → newest slice)
        $runningBalance = 0;
        $historyItems   = collect($history->items())->sortBy('date')->map(function ($item) use (&$runningBalance) {
            $runningBalance += (float) $item->balance;
            return array_merge($item->toArray(), ['running_balance' => $runningBalance]);
        })->sortByDesc('date')->values();

        return view('board-settlement.index', compact(
            'date', 'settlement', 'history', 'historyItems'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store / Upsert
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /board-settlement
     *
     * Upsert logic: one record per date. Re-submitting the same date
     * overwrites the previous entry without creating a duplicate.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSettlement($request);

        $settlement = BoardSettlement::firstOrNew(['date' => $data['date']]);
        $settlement->fill($data);
        $settlement->computeTotals();
        $settlement->save();

        return redirect()
            ->route('board-settlement.index', ['date' => $data['date']])
            ->with('success', 'Board settlement saved for ' . $data['date'] . '.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    private function validateSettlement(Request $request): array
    {
        $tierRules = [];

        foreach (array_keys(BoardSettlement::NLB_TIERS) as $col) {
            $tierRules[$col] = ['nullable', 'integer', 'min:0'];
        }
        foreach (array_keys(BoardSettlement::DLB_TIERS) as $col) {
            $tierRules[$col] = ['nullable', 'integer', 'min:0'];
        }
        foreach (array_keys(BoardSettlement::CASH_DENOMS) as $col) {
            $tierRules[$col] = ['nullable', 'integer', 'min:0'];
        }

        return $request->validate([
            'date'                   => ['required', 'date'],
            'total_tickets_received' => ['nullable', 'integer', 'min:0'],
            'total_ticket_value'     => ['nullable', 'numeric', 'min:0'],
            'bank_deposits'          => ['nullable', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string', 'max:500'],
            ...$tierRules,
        ]);
    }
}
