<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\LotteryStock;
use App\Models\SalesAssistant;
use App\Models\Winning;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // ADVANCED FILTER REPORT  (GET /reports)
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        [$from, $to, $preset] = $this->resolveDateRange($request);

        // ── Commission by day ────────────────────────────────────────────────
        $commByDay = LotteryStock::whereBetween('lottery_stocks.date', [$from, $to])
            ->join('lotteries', 'lotteries.id', '=', 'lottery_stocks.lottery_id')
            ->selectRaw('
                DATE(lottery_stocks.date) AS day,
                SUM(lottery_stocks.qty_issued * lotteries.unit_price
                    * lotteries.commission_rate / 100)               AS commission,
                SUM(lottery_stocks.qty_issued * lotteries.unit_price) AS gross_value
            ')
            ->groupByRaw('DATE(lottery_stocks.date)')
            ->pluck(null, 'day')
            ->map(fn ($r) => ['commission' => (float)$r->commission, 'gross_value' => (float)$r->gross_value]);

        // ── Expenses by day ──────────────────────────────────────────────────
        $expByDay = Expense::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) AS day, SUM(amount) AS amount')
            ->groupByRaw('DATE(date)')
            ->pluck('amount', 'day')
            ->map(fn ($v) => (float) $v);

        // ── Sales aggregates by day ──────────────────────────────────────────
        $salesByDay = DailySale::whereBetween('date', [$from, $to])
            ->selectRaw('
                DATE(date)              AS day,
                SUM(tickets_issued_val) AS issued,
                SUM(returns_val)        AS returns,
                SUM(winning_val)        AS winnings,
                SUM(cash_collected)     AS cash,
                SUM(balance)            AS outstanding,
                COUNT(*)                AS records
            ')
            ->groupByRaw('DATE(date)')
            ->get()->keyBy('day');

        // ── Winnings by day ──────────────────────────────────────────────────
        $winByDay = Winning::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) AS day, nlb_total, dlb_total, total_val')
            ->get()->keyBy('day');

        // ── Build full period rows (fill gaps with zeros) ────────────────────
        $rows = collect(CarbonPeriod::create($from, $to))
            ->map(function (Carbon $date) use ($commByDay, $expByDay, $salesByDay, $winByDay) {
                $d = $date->toDateString();
                $comm = $commByDay[$d] ?? ['commission' => 0, 'gross_value' => 0];
                $exp  = $expByDay[$d]  ?? 0;
                $sale = $salesByDay[$d] ?? null;
                $win  = $winByDay[$d]   ?? null;

                return [
                    'date'             => $d,
                    'gross_value'      => $comm['gross_value'],
                    'gross_commission' => $comm['commission'],
                    'total_expenses'   => $exp,
                    'net_profit'       => $comm['commission'] - $exp,
                    'issued_val'       => $sale ? (float) $sale->issued    : 0.0,
                    'returns_val'      => $sale ? (float) $sale->returns   : 0.0,
                    'winning_val'      => $sale ? (float) $sale->winnings  : 0.0,
                    'cash_collected'   => $sale ? (float) $sale->cash      : 0.0,
                    'outstanding'      => $sale ? (float) $sale->outstanding: 0.0,
                    'records'          => $sale ? (int)   $sale->records   : 0,
                    'nlb_winning'      => $win  ? (float) $win->nlb_total  : 0.0,
                    'dlb_winning'      => $win  ? (float) $win->dlb_total  : 0.0,
                    'total_winning'    => $win  ? (float) $win->total_val  : 0.0,
                ];
            });

        $totals = [
            'gross_value'      => $rows->sum('gross_value'),
            'gross_commission' => $rows->sum('gross_commission'),
            'total_expenses'   => $rows->sum('total_expenses'),
            'net_profit'       => $rows->sum('net_profit'),
            'issued_val'       => $rows->sum('issued_val'),
            'returns_val'      => $rows->sum('returns_val'),
            'winning_val'      => $rows->sum('winning_val'),
            'cash_collected'   => $rows->sum('cash_collected'),
            'outstanding'      => $rows->sum('outstanding'),
            'nlb_winning'      => $rows->sum('nlb_winning'),
            'dlb_winning'      => $rows->sum('dlb_winning'),
            'total_winning'    => $rows->sum('total_winning'),
            'records'          => $rows->sum('records'),
        ];

        // Chart datasets (only days that have data)
        $chartRows  = $rows->filter(fn ($r) => $r['records'] > 0 || $r['gross_commission'] > 0);
        $chartLabels = $chartRows->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d M'))->values();

        return view('reports.index', compact(
            'rows', 'totals', 'from', 'to', 'preset',
            'chartLabels', 'chartRows'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ASSISTANT PERFORMANCE  (GET /reports/assistants)
    // ─────────────────────────────────────────────────────────────────────────

    public function assistantPerformance(Request $request)
    {
        [$from, $to, $preset] = $this->resolveDateRange($request);

        $assistants = SalesAssistant::withSum(
                ['dailySales as total_issued' => fn ($q) => $q->whereBetween('date', [$from, $to])],
                'tickets_issued_val'
            )
            ->withSum(
                ['dailySales as total_cash' => fn ($q) => $q->whereBetween('date', [$from, $to])],
                'cash_collected'
            )
            ->withSum(
                ['dailySales as total_winning' => fn ($q) => $q->whereBetween('date', [$from, $to])],
                'winning_val'
            )
            ->withSum(
                ['dailySales as total_returns' => fn ($q) => $q->whereBetween('date', [$from, $to])],
                'returns_val'
            )
            ->withCount(
                ['dailySales as sale_days' => fn ($q) => $q->whereBetween('date', [$from, $to])]
            )
            ->orderByDesc('current_balance')
            ->get()
            ->map(function ($a) {
                $issued  = (float) ($a->total_issued  ?? 0);
                $cash    = (float) ($a->total_cash    ?? 0);
                $winning = (float) ($a->total_winning ?? 0);
                $returns = (float) ($a->total_returns ?? 0);
                $netBalance = $issued - ($cash + $winning + $returns);

                return array_merge($a->toArray(), [
                    'total_issued'      => $issued,
                    'total_cash'        => $cash,
                    'total_winning'     => $winning,
                    'total_returns'     => $returns,
                    'period_balance'    => $netBalance,
                    'collection_rate'   => $issued > 0 ? round(($cash / $issued) * 100, 1) : 0,
                ]);
            });

        // Per-day trend for the top assistant (for sparkline chart)
        $topAssistant = SalesAssistant::orderByDesc('current_balance')->first();
        $trend = $topAssistant
            ? DailySale::where('assistant_id', $topAssistant->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw('DATE(date) AS day, balance')
                ->orderBy('date')
                ->pluck('balance', 'day')
            : collect();

        return view('reports.assistant-performance', compact(
            'assistants', 'from', 'to', 'preset', 'topAssistant', 'trend'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF EXPORTS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * PDF: Daily Sales Summary
     * GET /reports/pdf/daily-sales?date=2026-01-01
     */
    public function pdfDailySales(Request $request)
    {
        $date  = $request->input('date', now()->toDateString());

        $sales = DailySale::with('assistant')
            ->whereDate('date', $date)
            ->orderBy('assistant_id')
            ->get();

        $winning  = Winning::where('date', $date)->first();
        $expenses = Expense::whereDate('date', $date)->get();

        $totals = [
            'issued'      => $sales->sum('tickets_issued_val'),
            'returns'     => $sales->sum('returns_val'),
            'winning'     => $sales->sum('winning_val'),
            'cash'        => $sales->sum('cash_collected'),
            'balance'     => $sales->sum('balance'),
            'expenses'    => $expenses->sum('amount'),
            'nlb_winning' => $winning?->nlb_total ?? 0,
            'dlb_winning' => $winning?->dlb_total ?? 0,
            'grand_winning'=> $winning?->total_val ?? 0,
        ];

        // Commission
        $commission = LotteryStock::whereDate('lottery_stocks.date', $date)
            ->join('lotteries', 'lotteries.id', '=', 'lottery_stocks.lottery_id')
            ->selectRaw('
                lotteries.name, lotteries.board,
                SUM(lottery_stocks.qty_issued * lotteries.unit_price * lotteries.commission_rate / 100) AS commission
            ')
            ->groupBy('lotteries.id', 'lotteries.name', 'lotteries.board')
            ->get();

        $totals['commission']  = $commission->sum('commission');
        $totals['net_profit']  = $totals['commission'] - $totals['expenses'];

        $pdf = Pdf::loadView('reports.pdf.daily-sales', compact('date', 'sales', 'winning', 'expenses', 'totals', 'commission'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('daily-sales-' . $date . '.pdf');
    }

    /**
     * PDF: Ledger Statement for one assistant
     * GET /reports/pdf/ledger/{assistant}?from=2026-01-01&to=2026-01-31
     */
    public function pdfLedger(Request $request, SalesAssistant $assistant)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $entries = $assistant->ledgers()
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $openingBalance = $assistant->ledgers()
            ->where('date', '<', $from)
            ->orderByDesc('date')->orderByDesc('id')
            ->value('running_balance') ?? 0;

        $pdf = Pdf::loadView('reports.pdf.ledger', compact('assistant', 'entries', 'from', 'to', 'openingBalance'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("ledger-{$assistant->id}-{$from}-{$to}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve from/to dates based on the `preset` or explicit `from`/`to` params.
     * Returns [$from (string), $to (string), $preset (string)].
     */
    private function resolveDateRange(Request $request): array
    {
        $preset = $request->input('preset', 'month');

        if ($request->filled('from') && $request->filled('to')) {
            $preset = 'custom';
            return [
                Carbon::parse($request->from)->toDateString(),
                Carbon::parse($request->to)->toDateString(),
                $preset,
            ];
        }

        return match ($preset) {
            'today'  => [now()->toDateString(),                    now()->toDateString(),                    'today'],
            'week'   => [now()->startOfWeek()->toDateString(),     now()->endOfWeek()->toDateString(),       'week'],
            'month'  => [now()->startOfMonth()->toDateString(),    now()->endOfMonth()->toDateString(),      'month'],
            'year'   => [now()->startOfYear()->toDateString(),     now()->endOfYear()->toDateString(),       'year'],
            'last7'  => [now()->subDays(6)->toDateString(),        now()->toDateString(),                    'last7'],
            'last30' => [now()->subDays(29)->toDateString(),       now()->toDateString(),                    'last30'],
            default  => [now()->startOfMonth()->toDateString(),    now()->endOfMonth()->toDateString(),      'month'],
        };
    }
}
