<x-layouts.app title="Dashboard">

    {{-- ── Top stat cards ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Net Profit --}}
        <div class="stat-card rounded-xl bg-white border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                    +12.4%
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900">
                Rs.{{ number_format($summary['profit']['net_profit'] ?? 0, 2) }}
            </p>
            <p class="mt-1 text-sm text-gray-500">Net Profit Today</p>
            <div class="mt-3 flex items-center gap-1 text-xs text-gray-400">
                <span class="font-medium text-gray-600">Commission:</span>
                Rs.{{ number_format($summary['commission']['gross_commission'] ?? 0, 2) }}
            </div>
        </div>

        {{-- Total Market Credit --}}
        <div class="stat-card rounded-xl bg-white border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600">
                    Owed
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900">
                Rs.{{ number_format($summary['sales']['total_outstanding_balance'] ?? 0, 2) }}
            </p>
            <p class="mt-1 text-sm text-gray-500">Total Market Credit</p>
            <div class="mt-3 flex items-center gap-1 text-xs text-gray-400">
                <span class="font-medium text-gray-600">Assistants:</span>
                {{ $summary['sales']['assistant_count'] ?? 0 }} active today
            </div>
        </div>

        {{-- Board Balance (Winnings) --}}
        <div class="stat-card rounded-xl bg-white border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-violet-50">
                    <svg class="h-6 w-6 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex gap-1">
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600">NLB</span>
                    <span class="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-600">DLB</span>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900">
                Rs.{{ number_format($summary['winnings']['total_val'] ?? 0, 2) }}
            </p>
            <p class="mt-1 text-sm text-gray-500">Total Board Winnings</p>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div class="rounded bg-blue-50 px-2 py-1 text-center">
                    <p class="font-semibold text-blue-700">Rs.{{ number_format($summary['winnings']['nlb_total'] ?? 0) }}</p>
                    <p class="text-blue-500">NLB</p>
                </div>
                <div class="rounded bg-orange-50 px-2 py-1 text-center">
                    <p class="font-semibold text-orange-700">Rs.{{ number_format($summary['winnings']['dlb_total'] ?? 0) }}</p>
                    <p class="text-orange-500">DLB</p>
                </div>
            </div>
        </div>

        {{-- Today's Expenses --}}
        <div class="stat-card rounded-xl bg-white border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-rose-50">
                    <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-600">
                    {{ $summary['expenses']['count'] ?? 0 }} items
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900">
                Rs.{{ number_format($summary['expenses']['total_expenses'] ?? 0, 2) }}
            </p>
            <p class="mt-1 text-sm text-gray-500">Today's Expenses</p>
            <div class="mt-3 text-xs text-gray-400">
                @if(!empty($summary['expenses']['items']) && count($summary['expenses']['items']))
                    Last: <span class="font-medium text-gray-600">{{ $summary['expenses']['items']->last()?->title }}</span>
                @else
                    <span>No expenses recorded</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Chart + Quick Summary ───────────────────────────────────────────── --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Profit / Loss Chart --}}
        <div class="lg:col-span-2 rounded-xl bg-white border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Profit / Loss — Last 14 Days</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Commission earned vs Expenses</p>
                </div>
                <select id="chartRange"
                        class="rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="14">14 Days</option>
                    <option value="30">30 Days</option>
                    <option value="7">7 Days</option>
                </select>
            </div>
            <canvas id="profitChart" height="240"></canvas>
        </div>

        {{-- Today's Quick Summary --}}
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Today's Summary</h2>
            <div class="space-y-3">
                @php
                    $rows = [
                        ['label' => 'Tickets Issued',  'value' => $summary['sales']['total_issued_val']     ?? 0, 'color' => 'text-blue-600'],
                        ['label' => 'Returns Value',   'value' => $summary['sales']['total_returns_val']    ?? 0, 'color' => 'text-gray-500'],
                        ['label' => 'Cash Collected',  'value' => $summary['sales']['total_cash_collected'] ?? 0, 'color' => 'text-emerald-600'],
                        ['label' => 'Total Winnings',  'value' => $summary['winnings']['total_val']         ?? 0, 'color' => 'text-violet-600'],
                        ['label' => 'Gross Commission','value' => $summary['commission']['gross_commission'] ?? 0, 'color' => 'text-blue-700'],
                        ['label' => 'Expenses',        'value' => $summary['expenses']['total_expenses']    ?? 0, 'color' => 'text-rose-600'],
                    ];
                @endphp
                @foreach($rows as $row)
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                        <span class="text-sm text-gray-500">{{ $row['label'] }}</span>
                        <span class="text-sm font-semibold {{ $row['color'] }}">
                            Rs.{{ number_format($row['value'], 2) }}
                        </span>
                    </div>
                @endforeach

                {{-- Net Profit divider --}}
                <div class="flex items-center justify-between pt-2">
                    <span class="text-sm font-bold text-gray-700">Net Profit</span>
                    @php $np = $summary['profit']['net_profit'] ?? 0; @endphp
                    <span class="rounded-full px-3 py-0.5 text-sm font-bold
                                 {{ $np >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        Rs.{{ number_format($np, 2) }}
                    </span>
                </div>
            </div>

            <a href="{{ route('daily-sales.index') }}"
               class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600
                      px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Enter Today's Sales
            </a>
        </div>
    </div>

    {{-- ── Cheque Alerts + Top Debtors ───────────────────────────────────────── --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Cheque Alert Widget ──────────────────────────────────────────────── --}}
        <div x-data="{ dismissed: {} }" class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    {{-- Pulsing bell if alerts exist --}}
                    @if($chequeAlerts->count())
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                    </span>
                    @endif
                    <h2 class="text-sm font-semibold text-gray-800">Cheque Alerts</h2>
                    @if($chequeAlerts->count())
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">
                            {{ $chequeAlerts->count() }} due
                        </span>
                    @endif
                </div>
                <a href="{{ route('cheques.index') }}"
                   class="text-xs font-medium text-blue-600 hover:text-blue-700">View All →</a>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($chequeAlerts as $alert)
                    <div x-show="!dismissed[{{ $alert['id'] }}]"
                         class="flex items-center justify-between px-5 py-3
                                {{ $alert['overdue'] ? 'bg-red-50/60' : 'bg-amber-50/40' }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                        {{ $alert['overdue'] ? 'bg-red-100' : 'bg-amber-100' }}">
                                <svg class="h-5 w-5 {{ $alert['overdue'] ? 'text-red-600' : 'text-amber-600' }}"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $alert['bank_name'] }}
                                    <span class="ml-1 font-mono text-xs text-gray-400">#{{ $alert['cheque_no'] }}</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Due: {{ \Carbon\Carbon::parse($alert['due_date'])->format('d M Y') }}
                                    @if($alert['overdue'])
                                        <span class="ml-1 font-semibold text-red-600">— OVERDUE</span>
                                    @else
                                        <span class="ml-1 text-amber-600">— {{ $alert['hours_left'] }}h left</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold {{ $alert['overdue'] ? 'text-red-700' : 'text-amber-700' }}">
                                Rs.{{ number_format($alert['amount'], 2) }}
                            </span>
                            <button @click="dismissed[{{ $alert['id'] }}] = true"
                                    class="rounded p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-6 w-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="mt-2 text-sm font-medium text-gray-600">No cheques due in 48 hours</p>
                        <p class="text-xs text-gray-400">You're all clear!</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Top Outstanding Assistants ──────────────────────────────────────── --}}
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-800">Top Outstanding Balances</h2>
                <a href="{{ route('reports.assistants') }}"
                   class="text-xs font-medium text-blue-600 hover:text-blue-700">Full Report →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($topDebtors ?? [] as $debtor)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700">
                                {{ strtoupper(substr($debtor->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $debtor->name }}</p>
                                <p class="text-xs text-gray-400">{{ $debtor->phone }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-red-600">Rs.{{ number_format($debtor->current_balance, 2) }}</p>
                            <a href="{{ route('assistants.ledger', $debtor) }}"
                               class="text-xs text-blue-500 hover:underline">Ledger</a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-gray-400">All balances are clear ✓</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Sales Table ──────────────────────────────────────────────── --}}
    <div class="mt-6 rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-800">Recent Daily Sales</h2>
            <a href="{{ route('daily-sales.index') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Assistant</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Issued Value</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Cash</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Winnings</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Balance</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentSales ?? [] as $sale)
                        <tr>
                            <td class="px-5 py-3 text-gray-600">{{ $sale->date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $sale->assistant->name }}</td>
                            <td class="px-5 py-3 text-right text-gray-700">Rs.{{ number_format($sale->tickets_issued_val, 2) }}</td>
                            <td class="px-5 py-3 text-right text-gray-700">Rs.{{ number_format($sale->cash_collected, 2) }}</td>
                            <td class="px-5 py-3 text-right text-violet-600">Rs.{{ number_format($sale->winning_val, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold
                                       {{ $sale->balance > 0 ? 'text-red-600' : ($sale->balance < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                                Rs.{{ number_format(abs($sale->balance), 2) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($sale->balance > 0)
                                    <span class="inline-block rounded-full balance-owes px-2 py-0.5 text-xs">Owes</span>
                                @elseif($sale->balance < 0)
                                    <span class="inline-block rounded-full balance-credit px-2 py-0.5 text-xs">Credit</span>
                                @else
                                    <span class="inline-block rounded-full balance-settled px-2 py-0.5 text-xs">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-gray-400">
                                No sales recorded today. <a href="{{ route('daily-sales.index') }}" class="text-blue-600 hover:underline">Add one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    // ── Profit / Loss chart ──────────────────────────────────────────────────
    const labels  = @json($chartLabels  ?? []);
    const commArr = @json($chartCommission ?? []);
    const expArr  = @json($chartExpenses   ?? []);
    const profArr = commArr.map((c, i) => c - (expArr[i] ?? 0));

    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Commission',
                    data: commArr,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Expenses',
                    data: expArr,
                    backgroundColor: 'rgba(244,63,94,0.6)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Net Profit',
                    data: profArr,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.35,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` Rs.${ctx.parsed.y.toLocaleString('en-LK', {minimumFractionDigits:2})}`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10 },
                        callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k',
                    },
                },
            },
        },
    });
    </script>
    @endpush

</x-layouts.app>
