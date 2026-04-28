<?php

namespace App\Http\Controllers;

use App\Models\BoardTransaction;
use App\Models\DailySale;
use App\Models\Expense;
use App\Models\Lottery;
use App\Models\LotteryStock;
use App\Models\SalesAssistant;
use App\Models\Winning;
use App\Models\BoardSettlement;
use App\Models\Cheque;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today      = today();
        $yesterday  = today()->subDay();
        $monthStart = today()->startOfMonth();
        $lastMoS    = today()->subMonth()->startOfMonth();
        $lastMoE    = today()->subMonth()->endOfMonth();

        // ══════════════════════════════════════════════════════════════════════
        // KPI CARDS
        // ══════════════════════════════════════════════════════════════════════

        // ── Net Profit (cash collected − expenses) ───────────────────────────
        $todayCash   = $this->cashForDate($today);
        $todayExp    = (float) Expense::whereDate('date', $today)->sum('amount');
        $todayProfit = $todayCash - $todayExp;

        $yestCash   = $this->cashForDate($yesterday);
        $yestExp    = (float) Expense::whereDate('date', $yesterday)->sum('amount');
        $yestProfit = $yestCash - $yestExp;
        $profitGrowth = $this->growthPct($todayProfit, $yestProfit);

        // ── Tickets Sold Today (qty issued × unit price = value) ─────────────
        $todayTicketsVal  = (float) DailySale::whereDate('date', $today)->sum('tickets_issued_val');
        $yestTicketsVal   = (float) DailySale::whereDate('date', $yesterday)->sum('tickets_issued_val');
        $ticketsGrowth    = $this->growthPct($todayTicketsVal, $yestTicketsVal);
        $todayTicketsQty  = (int) LotteryStock::whereDate('date', $today)->sum('qty_issued');

        // ── Board Outstanding (latest running balance) ────────────────────────
        $boardOutstanding = (float) (
            BoardTransaction::orderByDesc('date')->orderByDesc('id')->value('balance') ?? 0
        );
        // Month-start balance for comparison
        $monthStartBalance = (float) (
            BoardTransaction::whereDate('date', '<', $monthStart)
                ->orderByDesc('date')->orderByDesc('id')
                ->value('balance') ?? 0
        );
        $boardGrowth = $this->growthPct($boardOutstanding, $monthStartBalance);

        // ── Monthly Expenses ─────────────────────────────────────────────────
        $monthExpenses = (float) Expense::whereBetween('date', [$monthStart, $today])->sum('amount');
        $lastMonthExp  = (float) Expense::whereBetween('date', [$lastMoS, $lastMoE])->sum('amount');
        $expenseGrowth = $this->growthPct($monthExpenses, $lastMonthExp);

        // ══════════════════════════════════════════════════════════════════════
        // 7-DAY AREA CHART — Revenue (cash collected) vs Expenses
        // ══════════════════════════════════════════════════════════════════════
        $last7 = collect(range(6, 0))->map(fn ($d) => today()->subDays($d)->toDateString());

        $cashByDay = DailySale::whereBetween('date', [$last7->first(), $last7->last()])
            ->selectRaw('DATE(date) AS day, SUM(cash_collected) AS v')
            ->groupByRaw('DATE(date)')
            ->pluck('v', 'day');

        $expByDay = Expense::whereBetween('date', [$last7->first(), $last7->last()])
            ->selectRaw('DATE(date) AS day, SUM(amount) AS v')
            ->groupByRaw('DATE(date)')
            ->pluck('v', 'day');

        $chartLabels   = $last7->map(fn ($d) => Carbon::parse($d)->format('d M'))->values()->toArray();
        $chartRevenue  = $last7->map(fn ($d) => round((float) ($cashByDay[$d]  ?? 0), 2))->values()->toArray();
        $chartExpenses = $last7->map(fn ($d) => round((float) ($expByDay[$d]   ?? 0), 2))->values()->toArray();
        $chartProfit   = collect($chartRevenue)->map(fn ($v, $i) => round($v - $chartExpenses[$i], 2))->values()->toArray();

        // ══════════════════════════════════════════════════════════════════════
        // NLB vs DLB DOUGHNUT — monthly winning breakdown
        // ══════════════════════════════════════════════════════════════════════
        $nlbMonthly = (float) BoardTransaction::whereBetween('date', [$monthStart, $today])
            ->where('description', 'paid_bill')
            ->sum('nlb_winning');

        $dlbMonthly = (float) BoardTransaction::whereBetween('date', [$monthStart, $today])
            ->where('description', 'paid_bill')
            ->sum('dlb_winning');

        // Fallback to Winning model totals if board transactions not yet posted
        if ($nlbMonthly + $dlbMonthly === 0.0) {
            $nlbMonthly = (float) Winning::whereBetween('date', [$monthStart, $today])->sum('nlb_total');
            $dlbMonthly = (float) Winning::whereBetween('date', [$monthStart, $today])->sum('dlb_total');
        }

        // ══════════════════════════════════════════════════════════════════════
        // INVENTORY HEALTH BAR — top 5 lotteries by issued qty this month
        // ══════════════════════════════════════════════════════════════════════
        $inventoryData = LotteryStock::whereBetween('lottery_stocks.date', [$monthStart, $today])
            ->join('lotteries', 'lotteries.id', '=', 'lottery_stocks.lottery_id')
            ->selectRaw('lotteries.name, SUM(lottery_stocks.qty_issued) AS total_qty,
                         SUM(lottery_stocks.qty_issued * lotteries.unit_price) AS total_val')
            ->groupBy('lotteries.id', 'lotteries.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ══════════════════════════════════════════════════════════════════════
        // BOARD STATUS — NLB / DLB split + overall outstanding
        // ══════════════════════════════════════════════════════════════════════
        $boardNlbMonthPaid = $nlbMonthly;
        $boardDlbMonthPaid = $dlbMonthly;

        $boardMonthTicketValue = (float) BoardTransaction::whereBetween('date', [$monthStart, $today])
            ->where('description', 'get_tickets')
            ->sum('ticket_value');

        $boardMonthTotalPaid = (float) BoardTransaction::whereBetween('date', [$monthStart, $today])
            ->where('description', 'paid_bill')
            ->selectRaw('SUM(winning_amount + cash_amount + bank_deposits) AS v')
            ->value('v') ?? 0;

        $boardStatus = [
            'outstanding'       => $boardOutstanding,
            'month_ticket_val'  => $boardMonthTicketValue,
            'month_paid'        => $boardMonthTotalPaid,
            'nlb_winning_month' => $boardNlbMonthPaid,
            'dlb_winning_month' => $boardDlbMonthPaid,
        ];

        // ══════════════════════════════════════════════════════════════════════
        // ASSISTANT PERFORMANCE — top 5 by sales this month
        // ══════════════════════════════════════════════════════════════════════
        $topAssistants = SalesAssistant::select(
                'sales_assistants.id',
                'sales_assistants.name',
                'sales_assistants.phone',
                'sales_assistants.current_balance',
                DB::raw('SUM(daily_sales.tickets_issued_val) AS month_sales'),
                DB::raw('SUM(daily_sales.cash_collected)     AS month_cash'),
                DB::raw('COUNT(daily_sales.id)               AS sale_days')
            )
            ->join('daily_sales', 'daily_sales.assistant_id', '=', 'sales_assistants.id')
            ->whereBetween('daily_sales.date', [$monthStart, $today])
            ->groupBy('sales_assistants.id', 'sales_assistants.name',
                      'sales_assistants.phone', 'sales_assistants.current_balance')
            ->orderByDesc('month_sales')
            ->limit(5)
            ->get();

        // ══════════════════════════════════════════════════════════════════════
        // RECENT ACTIVITY FEED — last 10 items (settlements + expenses + stock)
        // ══════════════════════════════════════════════════════════════════════
        $recentActivity = $this->buildActivityFeed();

        // ══════════════════════════════════════════════════════════════════════
        // CHEQUE ALERTS (keep existing functionality)
        // ══════════════════════════════════════════════════════════════════════
        $chequeAlerts = Cheque::where('status', 'pending')
            ->whereBetween('due_date', [$today->toDateString(), $today->addDays(3)->toDateString()])
            ->orderBy('due_date')
            ->get()
            ->map(fn (Cheque $c) => [
                'id'         => $c->id,
                'bank_name'  => $c->bank_name,
                'cheque_no'  => $c->cheque_no,
                'amount'     => (float) $c->amount,
                'due_date'   => $c->due_date->toDateString(),
                'hours_left' => (int) now()->diffInHours($c->due_date->endOfDay(), false),
                'overdue'    => $c->due_date->isPast(),
            ]);

        return view('dashboard', compact(
            // KPI cards
            'todayProfit', 'profitGrowth',
            'todayTicketsVal', 'todayTicketsQty', 'ticketsGrowth',
            'boardOutstanding', 'boardGrowth',
            'monthExpenses', 'expenseGrowth',
            // Charts
            'chartLabels', 'chartRevenue', 'chartExpenses', 'chartProfit',
            'nlbMonthly', 'dlbMonthly',
            'inventoryData',
            // Widgets
            'boardStatus',
            'topAssistants',
            'recentActivity',
            'chequeAlerts',
        ));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function cashForDate($date): float
    {
        return (float) DailySale::whereDate('date', $date)->sum('cash_collected') ?? 0;
    }

    private function growthPct(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100.0 : 0.0;
        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    /**
     * Merge the last 10 notable events from three sources into one activity feed.
     */
    private function buildActivityFeed(): \Illuminate\Support\Collection
    {
        // Board transactions (last 8)
        $boardItems = BoardTransaction::with('settlement')
            ->orderByDesc('date')->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn ($t) => [
                'date'    => $t->date,
                'type'    => 'board',
                'label'   => $t->description_label,
                'amount'  => abs((float) $t->cr_amount),
                'sign'    => $t->cr_amount >= 0 ? '+' : '-',
                'badge'   => match($t->description) {
                    'get_tickets' => ['text' => 'Get Tickets', 'class' => 'bg-blue-100 text-blue-700'],
                    'paid_bill'   => ['text' => 'Paid Bill',   'class' => 'bg-green-100 text-green-700'],
                    default       => ['text' => 'Credit',      'class' => 'bg-amber-100 text-amber-700'],
                },
                'sub'     => $t->board_settlement_id ? 'via Settlement' : 'Manual entry',
                'icon'    => 'board',
            ]);

        // Expenses (last 5)
        $expItems = Expense::with('category')->orderByDesc('date')->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'date'   => $e->date,
                'type'   => 'expense',
                'label'  => $e->category?->name ?? 'Expense',
                'amount' => (float) $e->amount,
                'sign'   => '-',
                'badge'  => ['text' => 'Expense', 'class' => 'bg-rose-100 text-rose-700'],
                'sub'    => $e->category?->name ?? 'General',
                'icon'   => 'expense',
            ]);

        // Stock issues (last 5)
        $stockItems = LotteryStock::with('lottery')
            ->orderByDesc('date')->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'date'   => $s->date,
                'type'   => 'stock',
                'label'  => 'Stock Issued — ' . ($s->lottery?->name ?? 'Lottery'),
                'amount' => (float) ($s->qty_issued * ($s->lottery?->unit_price ?? 0)),
                'sign'   => '',
                'badge'  => ['text' => 'Stock Issue', 'class' => 'bg-indigo-100 text-indigo-700'],
                'sub'    => number_format($s->qty_issued) . ' tickets',
                'icon'   => 'stock',
            ]);

        return $boardItems->concat($expItems)->concat($stockItems)
            ->sortByDesc('date')
            ->values()
            ->take(10);
    }
}
