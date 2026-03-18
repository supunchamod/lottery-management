<x-layouts.app title="Reports">

    {{-- ── Header ───────────────────────────────────────────────────────────── --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Profit &amp; Loss Reports</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Filter by preset range or a custom date window.</p>
        </div>
        <a href="{{ route('reports.assistants') }}"
           class="flex items-center gap-2 rounded-lg border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Assistant Performance
        </a>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('reports.index') }}" id="filterForm"
          class="mb-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-4">

        {{-- Preset buttons --}}
        <div class="mb-3 flex flex-wrap gap-2">
            @foreach(['today'=>'Today','last7'=>'Last 7 Days','week'=>'This Week','last30'=>'Last 30 Days','month'=>'This Month','year'=>'This Year'] as $key=>$label)
                <a href="{{ route('reports.index', ['preset'=>$key]) }}"
                   class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                          {{ ($preset ?? 'month') === $key
                              ? 'bg-indigo-600 border-indigo-600 text-white'
                              : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-indigo-300 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Custom range + export buttons --}}
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="erp-input !w-40 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                       onchange="this.form.submit()">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="erp-input !w-40 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                       onchange="this.form.submit()">
            </div>
            <div class="ml-auto flex flex-wrap gap-2">
                {{-- Range PDF --}}
                <a href="{{ route('reports.pdf.range', ['from' => $from, 'to' => $to]) }}"
                   target="_blank"
                   class="btn-action flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-xs font-medium text-white hover:bg-rose-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    PDF Report
                </a>
                {{-- Excel export --}}
                <a href="{{ route('reports.excel', ['from' => $from, 'to' => $to]) }}"
                   class="btn-action flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel Export
                </a>
                {{-- Today's daily PDF --}}
                <a href="{{ route('reports.pdf.daily-sales', ['date' => now()->toDateString()]) }}"
                   target="_blank"
                   class="btn-action flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Today's PDF
                </a>
            </div>
        </div>
    </form>

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        @php
            $kpis = [
                ['label'=>'Tickets Issued', 'value'=>$totals['issued_val'],      'light'=>'bg-slate-50 text-slate-800',     'dark'=>'dark:bg-slate-800 dark:text-slate-100'],
                ['label'=>'Cash Collected', 'value'=>$totals['cash_collected'],  'light'=>'bg-teal-50 text-teal-700',       'dark'=>'dark:bg-teal-900/30 dark:text-teal-300'],
                ['label'=>'Expenses',       'value'=>$totals['total_expenses'],  'light'=>'bg-rose-50 text-rose-700',       'dark'=>'dark:bg-rose-900/30 dark:text-rose-300'],
                ['label'=>'Net Profit',     'value'=>$totals['net_profit'],      'light'=>$totals['net_profit']>=0?'bg-emerald-50 text-emerald-700':'bg-red-50 text-red-700', 'dark'=>$totals['net_profit']>=0?'dark:bg-emerald-900/30 dark:text-emerald-300':'dark:bg-red-900/30 dark:text-red-300'],
                ['label'=>'Winnings',       'value'=>$totals['total_winning'],   'light'=>'bg-violet-50 text-violet-700',   'dark'=>'dark:bg-violet-900/30 dark:text-violet-300'],
                ['label'=>'Credit',         'value'=>$totals['outstanding'],     'light'=>'bg-orange-50 text-orange-700',   'dark'=>'dark:bg-orange-900/30 dark:text-orange-300'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            <div class="rounded-2xl {{ $kpi['light'] }} {{ $kpi['dark'] }} border border-current/10 p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $kpi['label'] }}</p>
                <p class="mt-1 text-lg font-bold">
                    Rs.{{ number_format($kpi['value'], 2) }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- ── Chart ────────────────────────────────────────────────────────────── --}}
    <div class="mb-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                P&amp;L Chart —
                <span class="text-slate-400 dark:text-slate-500 font-normal">
                    {{ \Carbon\Carbon::parse($from)->format('d M Y') }} →
                    {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                </span>
            </h3>
        </div>
        <canvas id="reportChart" height="100"></canvas>
    </div>

    {{-- ── Daily Breakdown Table ───────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Daily Breakdown</h3>
            <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs text-slate-600 dark:text-slate-300">
                {{ $rows->count() }} days
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/60">
                        <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-400">Date</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-400">Issued</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-400">Returns</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-violet-500">Winnings</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-teal-500">Cash</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-rose-500">Expenses</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Net Profit</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-orange-500">Credit</th>
                        <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @foreach($rows as $r)
                        @if($r['records'] > 0 || $r['total_expenses'] > 0)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-4 py-2 font-medium text-slate-700 dark:text-slate-200">
                                {{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}
                                <span class="ml-1 text-slate-300 dark:text-slate-600">({{ $r['records'] }})</span>
                            </td>
                            <td class="px-4 py-2 text-right text-slate-600 dark:text-slate-300">{{ number_format($r['issued_val'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-orange-500">{{ number_format($r['returns_val'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-violet-600 dark:text-violet-400">{{ number_format($r['total_winning'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-teal-600 dark:text-teal-400">{{ number_format($r['cash_collected'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-rose-600 dark:text-rose-400">{{ number_format($r['total_expenses'], 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold
                                       {{ $r['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($r['net_profit'], 2) }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $r['outstanding'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-slate-300 dark:text-slate-600' }}">
                                {{ number_format($r['outstanding'], 0) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('reports.pdf.daily-sales', ['date' => $r['date']]) }}"
                                   target="_blank"
                                   class="rounded bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors">PDF</a>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-600 bg-indigo-50/60 dark:bg-indigo-900/20 font-bold text-xs">
                        <td class="px-4 py-3 text-indigo-700 dark:text-indigo-300">TOTALS ({{ $rows->where('records', '>', 0)->count() }} days)</td>
                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ number_format($totals['issued_val'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400">{{ number_format($totals['returns_val'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-violet-700 dark:text-violet-300">{{ number_format($totals['total_winning'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-teal-700 dark:text-teal-300">{{ number_format($totals['cash_collected'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-rose-700 dark:text-rose-300">{{ number_format($totals['total_expenses'], 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $totals['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
                            {{ number_format($totals['net_profit'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-orange-700 dark:text-orange-300">{{ number_format($totals['outstanding'], 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    const _isDark   = document.documentElement.classList.contains('dark');
    const gridColor = _isDark ? 'rgba(100,116,139,0.12)' : 'rgba(226,232,240,0.8)';
    const tickColor = _isDark ? '#64748b' : '#94a3b8';

    const labels    = @json($chartLabels->values());
    const cashData  = @json($chartRows->pluck('cash_collected')->values());
    const expData   = @json($chartRows->pluck('total_expenses')->values());
    const profData  = cashData.map((c, i) => c - (expData[i] || 0));

    new Chart(document.getElementById('reportChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Cash Collected', data: cashData, backgroundColor: _isDark ? 'rgba(20,184,166,0.6)' : 'rgba(20,184,166,0.7)', borderRadius: 4, order: 2 },
                { label: 'Expenses',       data: expData,  backgroundColor: _isDark ? 'rgba(244,63,94,0.5)'  : 'rgba(244,63,94,0.6)',  borderRadius: 4, order: 2 },
                {
                    label: 'Net Profit', data: profData, type: 'line',
                    borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: _isDark ? '#1e293b' : '#fff',
                    pointBorderColor: '#10b981',
                    pointRadius: 3,
                    fill: true, tension: 0.35, order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 }, boxWidth: 12, color: tickColor },
                },
                tooltip: {
                    backgroundColor: _isDark ? '#1e293b' : '#fff',
                    titleColor: _isDark ? '#f1f5f9' : '#1e293b',
                    bodyColor: _isDark ? '#94a3b8' : '#64748b',
                    borderColor: _isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    callbacks: { label: c => ` Rs.${c.parsed.y.toLocaleString('en-LK', {minimumFractionDigits:2})}` },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: tickColor },
                },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        font: { size: 10 }, color: tickColor,
                        callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k',
                    },
                },
            },
        },
    });
    </script>
    @endpush

</x-layouts.app>
