<x-layouts.app title="Executive Dashboard">

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 1 — KPI SUMMARY CARDS
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">

    @php
    $cards = [
        [
            'title'         => "Today's Net Profit",
            'value'         => 'Rs. ' . number_format($todayProfit, 2),
            'sub'           => 'Commission minus expenses',
            'growth'        => $profitGrowth,
            'positive_good' => true,
            'accent'        => 'from-emerald-500 to-teal-500',
            'icon_bg'       => 'bg-emerald-50',
            'icon_color'    => 'text-emerald-600',
            'icon'          => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        [
            'title'         => 'Tickets Sold Today',
            'value'         => number_format($todayTicketsQty) . ' units',
            'sub'           => 'Rs. ' . number_format($todayTicketsVal, 2) . ' value',
            'growth'        => $ticketsGrowth,
            'positive_good' => true,
            'accent'        => 'from-blue-500 to-indigo-500',
            'icon_bg'       => 'bg-blue-50',
            'icon_color'    => 'text-blue-600',
            'icon'          => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
        ],
        [
            'title'         => 'Board Outstanding',
            'value'         => 'Rs. ' . number_format(abs($boardOutstanding), 2),
            'sub'           => $boardOutstanding > 0 ? 'Owed to board' : ($boardOutstanding < 0 ? 'Board owes agent' : 'Fully settled'),
            'growth'        => $boardGrowth,
            'positive_good' => false,
            'accent'        => 'from-violet-500 to-purple-500',
            'icon_bg'       => 'bg-violet-50',
            'icon_color'    => 'text-violet-600',
            'icon'          => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        ],
        [
            'title'         => 'Monthly Expenses',
            'value'         => 'Rs. ' . number_format($monthExpenses, 2),
            'sub'           => now()->format('F Y') . ' total',
            'growth'        => $expenseGrowth,
            'positive_good' => false,
            'accent'        => 'from-rose-500 to-pink-500',
            'icon_bg'       => 'bg-rose-50',
            'icon_color'    => 'text-rose-600',
            'icon'          => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        ],
    ];
    @endphp

    @foreach($cards as $card)
    @php
        $isUp   = $card['growth'] >= 0;
        $isGood = $card['positive_good'] ? $isUp : !$isUp;
    @endphp
    <div class="relative rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden
                hover:shadow-md transition-all duration-200 group">
        {{-- Top gradient accent line --}}
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $card['accent'] }}"></div>
        <div class="p-5 pt-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $card['icon_bg'] }}
                            group-hover:scale-110 transition-transform duration-200">
                    <svg class="h-6 w-6 {{ $card['icon_color'] }}" fill="none" stroke="currentColor"
                         stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                {{-- Growth indicator --}}
                <div class="flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold
                    {{ $isGood ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                    <svg class="h-3 w-3 transition-transform {{ $isUp ? '' : 'rotate-180' }}"
                         fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                    </svg>
                    {{ abs($card['growth']) }}%
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-900 tracking-tight leading-none">
                {{ $card['value'] }}
            </p>
            <p class="mt-1.5 text-sm font-semibold text-gray-700">{{ $card['title'] }}</p>
            <p class="mt-0.5 text-xs text-gray-400">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 2 — CHARTS: Area (L) · Doughnut (M) · Inventory Bar (R)
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-5 mb-6">

    {{-- Revenue vs Expense — 7-day smooth area chart ────────────────── (3) --}}
    <div class="lg:col-span-3 rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Revenue vs Expense</h3>
                <p class="text-xs text-gray-400 mt-0.5">Last 7 days · commission earned vs costs incurred</p>
            </div>
            <div class="flex gap-4 text-xs">
                <span class="flex items-center gap-1.5 text-gray-500">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>Revenue
                </span>
                <span class="flex items-center gap-1.5 text-gray-500">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-rose-400"></span>Expenses
                </span>
                <span class="flex items-center gap-1.5 text-gray-500">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Profit
                </span>
            </div>
        </div>
        <div style="height:210px; position:relative;">
            <canvas id="areaChart"></canvas>
        </div>
    </div>

    {{-- NLB vs DLB Doughnut ──────────────────────────────────────────── (1) --}}
    <div class="lg:col-span-1 rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex flex-col">
        <h3 class="text-sm font-bold text-gray-800">Sales Split</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-4">NLB vs DLB · {{ now()->format('M Y') }}</p>
        <div class="relative flex-1" style="min-height:150px;">
            <canvas id="doughnutChart"></canvas>
        </div>
        @php
            $splitTotal = $nlbMonthly + $dlbMonthly;
            $nlbPct = $splitTotal > 0 ? round($nlbMonthly / $splitTotal * 100) : 50;
            $dlbPct = $splitTotal > 0 ? 100 - $nlbPct : 50;
        @endphp
        <div class="mt-4 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-gray-600">
                    <span class="h-2 w-5 rounded bg-blue-500"></span> NLB
                </span>
                <div class="text-right">
                    <span class="text-xs font-extrabold text-gray-800">{{ $nlbPct }}%</span>
                    <p class="text-xs text-gray-400">Rs. {{ number_format($nlbMonthly, 0) }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-gray-600">
                    <span class="h-2 w-5 rounded bg-violet-500"></span> DLB
                </span>
                <div class="text-right">
                    <span class="text-xs font-extrabold text-gray-800">{{ $dlbPct }}%</span>
                    <p class="text-xs text-gray-400">Rs. {{ number_format($dlbMonthly, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Health — horizontal bar ───────────────────────────── (1) --}}
    <div class="lg:col-span-1 rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex flex-col">
        <h3 class="text-sm font-bold text-gray-800">Inventory Health</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-4">Top 5 · issued this month</p>
        <div class="flex-1" style="min-height:150px; position:relative;">
            <canvas id="inventoryChart"></canvas>
        </div>
        <a href="{{ route('stock.index') }}"
           class="mt-3 text-center text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
            View All Stock →
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 3 — BOARD STATUS (L) + ASSISTANT PERFORMANCE (R)
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2 mb-6">

    {{-- Board Status Widget ──────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Board Status</h3>
                <p class="text-xs text-gray-400 mt-0.5">NLB · DLB outstanding &amp; monthly settlement</p>
            </div>
            <a href="{{ route('board-transactions.index') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                Full Ledger →
            </a>
        </div>

        <div class="p-5 space-y-4">
            {{-- Outstanding balance card --}}
            <div class="rounded-xl p-4 border
                {{ $boardStatus['outstanding'] > 0
                    ? 'bg-gradient-to-r from-red-50 to-rose-50 border-red-100'
                    : ($boardStatus['outstanding'] < 0
                        ? 'bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-100'
                        : 'bg-gray-50 border-gray-100') }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide
                            {{ $boardStatus['outstanding'] > 0 ? 'text-red-500'
                            : ($boardStatus['outstanding'] < 0 ? 'text-emerald-500' : 'text-gray-400') }}">
                            Current Outstanding
                        </p>
                        <p class="text-3xl font-extrabold mt-1 tracking-tight
                            {{ $boardStatus['outstanding'] > 0 ? 'text-red-700'
                            : ($boardStatus['outstanding'] < 0 ? 'text-emerald-700' : 'text-gray-500') }}">
                            Rs. {{ number_format(abs($boardStatus['outstanding']), 2) }}
                        </p>
                        <p class="text-xs mt-1
                            {{ $boardStatus['outstanding'] > 0 ? 'text-red-400'
                            : ($boardStatus['outstanding'] < 0 ? 'text-emerald-400' : 'text-gray-400') }}">
                            {{ $boardStatus['outstanding'] > 0 ? 'Owed to board — settle before next stock issue'
                             : ($boardStatus['outstanding'] < 0 ? 'Agent has excess credit with board' : 'Fully settled') }}
                        </p>
                    </div>
                    <div class="h-14 w-14 rounded-xl flex items-center justify-center shrink-0
                        {{ $boardStatus['outstanding'] > 0 ? 'bg-red-100'
                        : ($boardStatus['outstanding'] < 0 ? 'bg-emerald-100' : 'bg-gray-100') }}">
                        <svg class="h-7 w-7 {{ $boardStatus['outstanding'] > 0 ? 'text-red-600'
                                             : ($boardStatus['outstanding'] < 0 ? 'text-emerald-600' : 'text-gray-400') }}"
                             fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- NLB and DLB winning paid this month --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-blue-50 border border-blue-100 p-3.5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">NLB Winning</p>
                    </div>
                    <p class="text-xl font-extrabold text-blue-800">
                        Rs. {{ number_format($boardStatus['nlb_winning_month'], 0) }}
                    </p>
                    <p class="text-xs text-blue-400 mt-0.5">Paid · {{ now()->format('M Y') }}</p>
                </div>
                <div class="rounded-xl bg-purple-50 border border-purple-100 p-3.5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">DLB Winning</p>
                    </div>
                    <p class="text-xl font-extrabold text-purple-800">
                        Rs. {{ number_format($boardStatus['dlb_winning_month'], 0) }}
                    </p>
                    <p class="text-xs text-purple-400 mt-0.5">Paid · {{ now()->format('M Y') }}</p>
                </div>
            </div>

            {{-- Settlement progress bar --}}
            @php
                $settledPct = $boardStatus['month_ticket_val'] > 0
                    ? min(100, round($boardStatus['month_paid'] / $boardStatus['month_ticket_val'] * 100))
                    : 0;
            @endphp
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                    <span class="font-medium">Month Settlement Progress</span>
                    <span class="font-bold {{ $settledPct >= 100 ? 'text-emerald-600' : ($settledPct >= 60 ? 'text-amber-600' : 'text-red-500') }}">
                        {{ $settledPct }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                    <div class="h-2.5 rounded-full transition-all duration-700
                        {{ $settledPct >= 100 ? 'bg-gradient-to-r from-emerald-400 to-teal-500'
                         : ($settledPct >= 60  ? 'bg-gradient-to-r from-amber-400 to-yellow-500'
                         : 'bg-gradient-to-r from-red-400 to-rose-500') }}"
                         style="width: {{ $settledPct }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-1.5">
                    <span>Paid: <span class="font-medium text-gray-600">Rs. {{ number_format($boardStatus['month_paid'], 0) }}</span></span>
                    <span>Total: <span class="font-medium text-gray-600">Rs. {{ number_format($boardStatus['month_ticket_val'], 0) }}</span></span>
                </div>
            </div>
        </div>

        <div class="px-5 pb-5 grid grid-cols-2 gap-3">
            <a href="{{ route('board-settlement.index') }}"
               class="flex items-center justify-center gap-2 rounded-xl bg-slate-800 hover:bg-slate-700
                      text-white text-xs font-bold py-2.5 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Settlement
            </a>
            <a href="{{ route('board-transactions.index') }}"
               class="flex items-center justify-center gap-2 rounded-xl border border-gray-200
                      text-gray-600 text-xs font-bold py-2.5 hover:bg-gray-50 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                View Ledger
            </a>
        </div>
    </div>

    {{-- Assistant Performance Table ─────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Assistant Performance</h3>
                <p class="text-xs text-gray-400 mt-0.5">Top sellers · {{ now()->format('F Y') }}</p>
            </div>
            <a href="{{ route('reports.assistants') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                Full Report →
            </a>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($topAssistants as $idx => $asst)
            @php
                $maxSales   = (float) ($topAssistants->max('month_sales') ?: 1);
                $barPct     = round($asst->month_sales / $maxSales * 100);
                $rankColors = [
                    'bg-gradient-to-br from-yellow-400 to-amber-500',
                    'bg-gradient-to-br from-gray-400 to-gray-500',
                    'bg-gradient-to-br from-amber-600 to-yellow-700',
                    'bg-gradient-to-br from-slate-300 to-slate-400',
                    'bg-gradient-to-br from-slate-300 to-slate-400',
                ];
            @endphp
            <div class="px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                <div class="flex items-center gap-3">
                    {{-- Rank --}}
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                                {{ $rankColors[$idx] ?? 'bg-gray-200' }} text-white text-xs font-extrabold shadow-sm">
                        {{ $idx + 1 }}
                    </div>
                    {{-- Avatar --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-xs font-bold shadow-sm">
                        {{ strtoupper(mb_substr($asst->name, 0, 2)) }}
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $asst->name }}</p>
                            <p class="text-sm font-extrabold text-gray-900 ml-2 shrink-0">
                                Rs. {{ number_format($asst->month_sales, 0) }}
                            </p>
                        </div>
                        {{-- Progress bar --}}
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500
                                            transition-all duration-700"
                                     style="width: {{ $barPct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0 w-6 text-right">{{ $asst->sale_days }}d</span>
                        </div>
                    </div>
                </div>
                <div class="mt-1.5 ml-[5.5rem] flex items-center gap-3 text-xs text-gray-400">
                    <span>Cash collected:
                        <span class="text-emerald-700 font-semibold">Rs. {{ number_format($asst->month_cash, 0) }}</span>
                    </span>
                    @if($asst->current_balance > 0)
                    <span class="flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                        <span class="text-red-500 font-medium">Owes Rs. {{ number_format($asst->current_balance, 0) }}</span>
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-14 text-center text-gray-400 text-sm">
                <svg class="mx-auto h-10 w-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                No sales recorded this month.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 4 — ACTIVITY FEED (L·L) + CHEQUE ALERTS + QUICK ACTIONS (R)
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    {{-- Recent Activity Feed ─────────────────────────────────────── (span 2) --}}
    <div class="lg:col-span-2 rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Recent Activity</h3>
                <p class="text-xs text-gray-400 mt-0.5">Latest 10 transactions across all modules</p>
            </div>
            <a href="{{ route('board-transactions.index') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700">Board Ledger →</a>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($recentActivity as $item)
            @php
                $iconConfig = match($item['type']) {
                    'board'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'path' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    'expense' => ['bg' => 'bg-rose-50',   'text' => 'text-rose-600',   'path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    default   => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'path' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                };
            @endphp
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/70 transition-colors">
                {{-- Icon --}}
                <div class="shrink-0 flex h-9 w-9 items-center justify-center rounded-xl {{ $iconConfig['bg'] }}">
                    <svg class="h-4.5 w-4.5 {{ $iconConfig['text'] }}" fill="none" stroke="currentColor"
                         stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconConfig['path'] }}"/>
                    </svg>
                </div>
                {{-- Label + badge + meta --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-gray-800 truncate max-w-xs">{{ $item['label'] }}</p>
                        <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                     {{ $item['badge']['class'] }}">
                            {{ $item['badge']['text'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}
                        · {{ $item['sub'] }}
                    </p>
                </div>
                {{-- Amount --}}
                <div class="shrink-0 text-right">
                    <p class="text-sm font-bold whitespace-nowrap
                        {{ $item['sign'] === '-' ? 'text-red-600'
                         : ($item['sign'] === '+' ? 'text-gray-900' : 'text-indigo-700') }}">
                        {{ $item['sign'] === '-' ? '−' : ($item['sign'] === '+' ? '+' : '') }}Rs.&nbsp;{{ number_format($item['amount'], 2) }}
                    </p>
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-gray-400">
                <svg class="mx-auto h-10 w-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">No recent activity.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Cheque Alerts + Quick Actions ──────────────────────────────── (span 1) --}}
    <div class="space-y-5">

        {{-- Cheque Alerts --}}
        <div x-data="{ dismissed: {} }"
             class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    @if($chequeAlerts->count())
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                    @endif
                    <h3 class="text-sm font-bold text-gray-800">Cheque Alerts</h3>
                    @if($chequeAlerts->count())
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-extrabold text-amber-700">
                        {{ $chequeAlerts->count() }}
                    </span>
                    @endif
                </div>
                <a href="{{ route('cheques.index') }}"
                   class="text-xs font-medium text-blue-600 hover:text-blue-700">View All →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($chequeAlerts as $alert)
                <div x-show="!dismissed[{{ $alert['id'] }}]"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center justify-between px-5 py-3
                            {{ $alert['overdue'] ? 'bg-red-50/60' : 'bg-amber-50/40' }}">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate">
                            {{ $alert['bank_name'] }}
                            <span class="font-mono font-normal text-gray-400 text-[10px]">#{{ $alert['cheque_no'] }}</span>
                        </p>
                        <p class="text-xs font-semibold mt-0.5
                            {{ $alert['overdue'] ? 'text-red-600' : 'text-amber-600' }}">
                            {{ $alert['overdue'] ? 'OVERDUE' : $alert['hours_left'].'h left' }}
                            · {{ \Carbon\Carbon::parse($alert['due_date'])->format('d M') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 ml-2 shrink-0">
                        <span class="text-xs font-extrabold {{ $alert['overdue'] ? 'text-red-700' : 'text-amber-700' }}">
                            Rs.{{ number_format($alert['amount'], 0) }}
                        </span>
                        <button @click="dismissed[{{ $alert['id'] }}] = true"
                                class="rounded-full p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition-colors">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center">
                    <div class="mx-auto h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center mb-2">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-500">All clear — no cheques due</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions Panel --}}
        <div class="rounded-2xl bg-gradient-to-b from-slate-800 to-slate-900 p-5 shadow-lg">
            <div class="flex items-center gap-2 mb-4">
                <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <h3 class="text-sm font-bold text-white">Quick Actions</h3>
            </div>
            <div class="space-y-2">
                @php
                $actions = [
                    ['route' => 'daily-sales.index',        'label' => 'Enter Daily Sales',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'hover:bg-blue-600/30 border-blue-500/30 text-blue-300'],
                    ['route' => 'board-settlement.index',   'label' => 'Board Settlement',    'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'hover:bg-violet-600/30 border-violet-500/30 text-violet-300'],
                    ['route' => 'board-transactions.index', 'label' => 'Board Ledger',        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'hover:bg-indigo-600/30 border-indigo-500/30 text-indigo-300'],
                    ['route' => 'expenses.index',           'label' => 'Add Expense',         'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'hover:bg-rose-600/30 border-rose-500/30 text-rose-300'],
                    ['route' => 'stock.create',             'label' => 'Issue Stock',          'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'hover:bg-emerald-600/30 border-emerald-500/30 text-emerald-300'],
                    ['route' => 'reports.index',            'label' => 'View Reports',         'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'hover:bg-gray-600/30 border-gray-500/30 text-gray-300'],
                ];
                @endphp
                @foreach($actions as $action)
                <a href="{{ route($action['route']) }}"
                   class="flex items-center gap-2.5 w-full rounded-xl border bg-white/5 {{ $action['color'] }}
                          px-3.5 py-2.5 text-xs font-semibold transition-all duration-150">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}"/>
                    </svg>
                    {{ $action['label'] }}
                    <svg class="h-3 w-3 ml-auto opacity-50" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
            <p class="mt-4 text-center text-xs text-slate-500">
                {{ now()->format('l, d M Y') }}
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Global chart defaults (Inter font) ──────────────────────────────────────
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#94a3b8';

const labels   = @json($chartLabels);
const revenue  = @json($chartRevenue);
const expenses = @json($chartExpenses);
const profit   = @json($chartProfit);

// ════════════════════════════════════════════════════════════════════════════
// 1. REVENUE VS EXPENSE — smooth area chart
// ════════════════════════════════════════════════════════════════════════════
(function () {
    const ctx = document.getElementById('areaChart').getContext('2d');

    const mkGrad = (ctx, c1, c2) => {
        const g = ctx.createLinearGradient(0, 0, 0, 210);
        g.addColorStop(0, c1); g.addColorStop(1, c2); return g;
    };

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenue,
                    borderColor: '#3b82f6',
                    backgroundColor: mkGrad(ctx, 'rgba(59,130,246,0.22)', 'rgba(59,130,246,0)'),
                    borderWidth: 2.5, fill: true, tension: 0.45,
                    pointRadius: 4, pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6', pointBorderWidth: 2,
                    pointHoverRadius: 6, order: 2,
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    borderColor: '#f43f5e',
                    backgroundColor: mkGrad(ctx, 'rgba(244,63,94,0.18)', 'rgba(244,63,94,0)'),
                    borderWidth: 2, fill: true, tension: 0.45,
                    pointRadius: 4, pointBackgroundColor: '#fff',
                    pointBorderColor: '#f43f5e', pointBorderWidth: 2,
                    pointHoverRadius: 6, order: 3,
                },
                {
                    label: 'Net Profit',
                    data: profit,
                    borderColor: '#10b981',
                    backgroundColor: mkGrad(ctx, 'rgba(16,185,129,0.18)', 'rgba(16,185,129,0)'),
                    borderWidth: 2, fill: true, tension: 0.45,
                    borderDash: [5, 3],
                    pointRadius: 4, pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981', pointBorderWidth: 2,
                    pointHoverRadius: 6, order: 1,
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b', titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9', borderColor: '#334155',
                    borderWidth: 1, padding: 12, cornerRadius: 10,
                    callbacks: {
                        label: c => ` Rs.${Number(c.parsed.y).toLocaleString('en-LK', { minimumFractionDigits: 2 })}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 10 } },
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    border: { display: false, dash: [3, 3] },
                    ticks: {
                        callback: v => v >= 1000 ? 'Rs.' + (v / 1000).toFixed(0) + 'k' : 'Rs.' + v,
                    },
                },
            },
        },
    });
})();

// ════════════════════════════════════════════════════════════════════════════
// 2. NLB vs DLB DOUGHNUT
// ════════════════════════════════════════════════════════════════════════════
(function () {
    const ctx = document.getElementById('doughnutChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['NLB', 'DLB'],
            datasets: [{
                data: [{{ $nlbMonthly ?: 1 }}, {{ $dlbMonthly ?: 1 }}],
                backgroundColor: ['#3b82f6', '#8b5cf6'],
                hoverBackgroundColor: ['#2563eb', '#7c3aed'],
                borderWidth: 3, borderColor: '#fff',
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '75%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b', bodyColor: '#f1f5f9',
                    padding: 10, cornerRadius: 8,
                    callbacks: {
                        label: c => ` Rs.${Number(c.parsed).toLocaleString('en-LK', { minimumFractionDigits: 2 })}`,
                    },
                },
            },
        },
    });
})();

// ════════════════════════════════════════════════════════════════════════════
// 3. INVENTORY HEALTH — horizontal bar
// ════════════════════════════════════════════════════════════════════════════
(function () {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($inventoryData->pluck('name')),
            datasets: [{
                label: 'Tickets',
                data: @json($inventoryData->pluck('total_qty')),
                backgroundColor: ['#6366f1','#3b82f6','#10b981','#f59e0b','#f43f5e'],
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b', bodyColor: '#f1f5f9',
                    padding: 10, cornerRadius: 8,
                    callbacks: {
                        label: c => ` ${Number(c.parsed.x).toLocaleString()} tickets`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false }, border: { display: false },
                    ticks: {
                        font: { size: 9 },
                        callback: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v,
                    },
                },
                y: {
                    grid: { display: false }, border: { display: false },
                    ticks: {
                        font: { size: 9 },
                        callback: function (val) {
                            const lbl = this.getLabelForValue(val);
                            return lbl.length > 12 ? lbl.slice(0, 12) + '…' : lbl;
                        },
                    },
                },
            },
        },
    });
})();
</script>
@endpush

</x-layouts.app>
