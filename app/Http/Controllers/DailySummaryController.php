<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\Winning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DailySummaryController
 *
 * Produces the daily P&L summary that mirrors the Excel "W.R Soysa" report:
 *
 *   Net Profit = Cash Collected − Total Daily Expenses
 *
 * Additional aggregates (total sales, total winnings, cash collected,
 * outstanding assistant balances) are also returned so the dashboard
 * can replicate the full Excel summary page.
 */
class DailySummaryController extends Controller
{
    /**
     * GET /daily-summary?date=2026-01-01
     *
     * If no date is supplied, today's date is used.
     */
    public function show(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());

        // ── 1. Sales aggregates ───────────────────────────────────────────────
        $salesAgg = DailySale::whereDate('date', $date)
            ->selectRaw('
                COUNT(*)                   AS assistant_count,
                SUM(tickets_issued_val)    AS total_issued_val,
                SUM(returns_val)           AS total_returns_val,
                SUM(winning_val)           AS total_winning_val,
                SUM(cash_collected)        AS total_cash_collected,
                SUM(balance)               AS total_outstanding_balance
            ')
            ->first();

        // ── 2. Daily expenses ─────────────────────────────────────────────────
        $expenseAgg = Expense::whereDate('date', $date)
            ->selectRaw('
                SUM(amount) AS total_expenses,
                COUNT(*)    AS expense_count
            ')
            ->first();

        $totalExpenses      = (float) ($expenseAgg->total_expenses ?? 0);
        $totalCashCollected = (float) ($salesAgg->total_cash_collected ?? 0);

        // ── 3. Net profit ─────────────────────────────────────────────────────
        //   Net Profit = Cash Collected − Daily Expenses
        $netProfit = $totalCashCollected - $totalExpenses;

        // ── 4. Winning totals for the day ─────────────────────────────────────
        $winning = Winning::where('date', $date)->first();

        // ── 5. Assemble response ──────────────────────────────────────────────
        return response()->json([
            'date' => $date,

            'sales' => [
                'assistant_count'          => (int)   ($salesAgg->assistant_count       ?? 0),
                'total_issued_val'         => (float)  ($salesAgg->total_issued_val      ?? 0),
                'total_returns_val'        => (float)  ($salesAgg->total_returns_val     ?? 0),
                'total_winning_val'        => (float)  ($salesAgg->total_winning_val     ?? 0),
                'total_cash_collected'     => (float)  ($salesAgg->total_cash_collected  ?? 0),
                'total_outstanding_balance'=> (float)  ($salesAgg->total_outstanding_balance ?? 0),
            ],

            'winnings' => [
                'nlb_total' => $winning ? (float) $winning->nlb_total : 0.0,
                'dlb_total' => $winning ? (float) $winning->dlb_total : 0.0,
                'total_val' => $winning ? (float) $winning->total_val : 0.0,
            ],

            'expenses' => [
                'count'          => (int)   ($expenseAgg->expense_count  ?? 0),
                'total_expenses' => (float)  $totalExpenses,
                'items'          => Expense::whereDate('date', $date)->get(),
            ],

            'profit' => [
                // Net Profit = Cash Collected − Daily Expenses
                'total_cash_collected' => (float) $totalCashCollected,
                'total_expenses'       => (float) $totalExpenses,
                'net_profit'           => (float) $netProfit,
                'status'               => $netProfit >= 0 ? 'profit' : 'loss',
            ],
        ]);
    }

    /**
     * GET /daily-summary/range?from=2026-01-01&to=2026-01-31
     *
     * Returns the same P&L broken down by day for a date range.
     * Useful for the monthly overview tab in the Excel report.
     */
    public function range(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->from;
        $to   = $request->to;

        // Per-day expense totals
        $expensesByDay = Expense::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) AS day, SUM(amount) AS total_expenses')
            ->groupByRaw('DATE(date)')
            ->pluck('total_expenses', 'day');

        // Per-day sales totals
        $salesByDay = DailySale::whereBetween('date', [$from, $to])
            ->selectRaw('
                DATE(date) AS day,
                SUM(tickets_issued_val) AS issued,
                SUM(cash_collected)     AS cash,
                SUM(winning_val)        AS winnings,
                SUM(balance)            AS outstanding
            ')
            ->groupByRaw('DATE(date)')
            ->get()
            ->keyBy('day');

        // Merge into a unified daily array
        $allDays = $expensesByDay->keys()
            ->merge($salesByDay->keys())
            ->unique()
            ->sort()
            ->values();

        $summary = $allDays->map(function ($day) use ($expensesByDay, $salesByDay) {
            $expenses = (float) ($expensesByDay[$day] ?? 0);
            $sales    = $salesByDay[$day] ?? null;
            $cash     = $sales ? (float) $sales->cash : 0.0;

            return [
                'date'           => $day,
                'total_expenses' => $expenses,
                'net_profit'     => $cash - $expenses,
                'issued_val'     => $sales ? (float) $sales->issued      : 0.0,
                'cash_collected' => $cash,
                'winning_val'    => $sales ? (float) $sales->winnings    : 0.0,
                'outstanding'    => $sales ? (float) $sales->outstanding : 0.0,
            ];
        });

        $totals = [
            'total_expenses' => $summary->sum('total_expenses'),
            'net_profit'     => $summary->sum('net_profit'),
            'issued_val'     => $summary->sum('issued_val'),
            'cash_collected' => $summary->sum('cash_collected'),
            'winning_val'    => $summary->sum('winning_val'),
            'outstanding'    => $summary->sum('outstanding'),
        ];

        return response()->json([
            'from'    => $from,
            'to'      => $to,
            'days'    => $summary,
            'totals'  => $totals,
        ]);
    }
}
