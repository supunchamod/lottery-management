<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\LotteryStock;
use App\Models\Winning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $date = now()->toDateString();

        // ── Today's summary (reuse DailySummaryController logic inline) ──────
        $salesAgg = DailySale::whereDate('date', $date)
            ->selectRaw('
                COUNT(*) AS assistant_count,
                SUM(tickets_issued_val) AS total_issued_val,
                SUM(returns_val) AS total_returns_val,
                SUM(winning_val) AS total_winning_val,
                SUM(cash_collected) AS total_cash_collected,
                SUM(balance) AS total_outstanding_balance
            ')->first();

        $commissionRows = LotteryStock::whereDate('lottery_stocks.date', $date)
            ->join('lotteries', 'lotteries.id', '=', 'lottery_stocks.lottery_id')
            ->selectRaw('SUM(lottery_stocks.qty_issued * lotteries.unit_price * lotteries.commission_rate / 100) AS commission')
            ->first();

        $grossCommission = (float) ($commissionRows->commission ?? 0);

        $expenseAgg = Expense::whereDate('date', $date)
            ->selectRaw('SUM(amount) AS total_expenses, COUNT(*) AS expense_count')
            ->first();

        $totalExpenses = (float) ($expenseAgg->total_expenses ?? 0);
        $winning       = Winning::where('date', $date)->first();

        $summary = [
            'sales'      => [
                'assistant_count'           => (int)   ($salesAgg->assistant_count           ?? 0),
                'total_issued_val'          => (float) ($salesAgg->total_issued_val           ?? 0),
                'total_returns_val'         => (float) ($salesAgg->total_returns_val          ?? 0),
                'total_winning_val'         => (float) ($salesAgg->total_winning_val          ?? 0),
                'total_cash_collected'      => (float) ($salesAgg->total_cash_collected       ?? 0),
                'total_outstanding_balance' => (float) ($salesAgg->total_outstanding_balance  ?? 0),
            ],
            'winnings'   => [
                'nlb_total' => $winning ? (float) $winning->nlb_total : 0.0,
                'dlb_total' => $winning ? (float) $winning->dlb_total : 0.0,
                'total_val' => $winning ? (float) $winning->total_val : 0.0,
            ],
            'commission' => ['gross_commission' => $grossCommission],
            'expenses'   => [
                'count'          => (int)   ($expenseAgg->expense_count ?? 0),
                'total_expenses' => $totalExpenses,
                'items'          => Expense::whereDate('date', $date)->latest()->get(),
            ],
            'profit'     => [
                'gross_commission' => $grossCommission,
                'total_expenses'   => $totalExpenses,
                'net_profit'       => $grossCommission - $totalExpenses,
            ],
        ];

        // ── Recent sales (today) ──────────────────────────────────────────────
        $recentSales = DailySale::with('assistant')
            ->whereDate('date', $date)
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // ── Chart data: last 14 days ──────────────────────────────────────────
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->toDateString());

        $commByDay = LotteryStock::whereBetween('lottery_stocks.date', [$days->first(), $days->last()])
            ->join('lotteries', 'lotteries.id', '=', 'lottery_stocks.lottery_id')
            ->selectRaw('DATE(lottery_stocks.date) AS day, SUM(lottery_stocks.qty_issued * lotteries.unit_price * lotteries.commission_rate / 100) AS v')
            ->groupByRaw('DATE(lottery_stocks.date)')
            ->pluck('v', 'day');

        $expByDay = Expense::whereBetween('date', [$days->first(), $days->last()])
            ->selectRaw('DATE(date) AS day, SUM(amount) AS v')
            ->groupByRaw('DATE(date)')
            ->pluck('v', 'day');

        return view('dashboard', [
            'summary'          => $summary,
            'recentSales'      => $recentSales,
            'chartLabels'      => $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->values()->toArray(),
            'chartCommission'  => $days->map(fn ($d) => (float) ($commByDay[$d] ?? 0))->values()->toArray(),
            'chartExpenses'    => $days->map(fn ($d) => (float) ($expByDay[$d]  ?? 0))->values()->toArray(),
        ]);
    }
}
