<x-layouts.app title="Reports">

    {{-- ── Header + Preset pills ───────────────────────────────────────────── --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Profit & Loss Reports</h2>
            <p class="text-sm text-gray-500">Filter by preset range or a custom date window.</p>
        </div>
        <a href="{{ route('reports.assistants') }}"
           class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Assistant Performance
        </a>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('reports.index') }}" id="filterForm"
          class="mb-5 rounded-xl bg-white border border-gray-100 shadow-sm p-4">

        {{-- Preset buttons --}}
        <div class="mb-3 flex flex-wrap gap-2">
            @foreach(['today'=>'Today','last7'=>'Last 7 Days','week'=>'This Week','last30'=>'Last 30 Days','month'=>'This Month','year'=>'This Year'] as $key=>$label)
                <a href="{{ route('reports.index', ['preset'=>$key]) }}"
                   class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                          {{ ($preset ?? 'month') === $key
                              ? 'bg-blue-600 border-blue-600 text-white'
                              : 'border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Custom range --}}
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="erp-input !w-40" onchange="this.form.submit()">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="erp-input !w-40" onchange="this.form.submit()">
            </div>
            <div class="ml-auto flex gap-2">
                {{-- PDF export for today --}}
                <a href="{{ route('reports.pdf.daily-sales', ['date' => now()->toDateString()]) }}"
                   target="_blank"
                   class="flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Today's PDF
                </a>
                <button type="button" onclick="window.print()"
                        class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </form>

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        @php
            $kpis = [
                ['label'=>'Gross Value',   'value'=>$totals['gross_value'],      'color'=>'text-gray-800',    'bg'=>'bg-gray-50'],
                ['label'=>'Commission',    'value'=>$totals['gross_commission'],  'color'=>'text-blue-700',    'bg'=>'bg-blue-50'],
                ['label'=>'Expenses',      'value'=>$totals['total_expenses'],    'color'=>'text-rose-700',    'bg'=>'bg-rose-50'],
                ['label'=>'Net Profit',    'value'=>$totals['net_profit'],        'color'=>$totals['net_profit']>=0?'text-emerald-700':'text-red-700', 'bg'=>$totals['net_profit']>=0?'bg-emerald-50':'bg-red-50'],
                ['label'=>'Cash Collected','value'=>$totals['cash_collected'],    'color'=>'text-teal-700',    'bg'=>'bg-teal-50'],
                ['label'=>'Outstanding',   'value'=>$totals['outstanding'],       'color'=>'text-orange-700',  'bg'=>'bg-orange-50'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            <div class="rounded-xl {{ $kpi['bg'] }} border border-current/10 p-4">
                <p class="text-xs font-medium text-gray-500">{{ $kpi['label'] }}</p>
                <p class="mt-1 text-lg font-bold {{ $kpi['color'] }}">
                    Rs.{{ number_format($kpi['value'], 2) }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- ── Chart ────────────────────────────────────────────────────────────── --}}
    <div class="mb-5 rounded-xl bg-white border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">
                P&L Chart —
                <span class="text-gray-400 font-normal">
                    {{ \Carbon\Carbon::parse($from)->format('d M Y') }} →
                    {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                </span>
            </h3>
        </div>
        <canvas id="reportChart" height="100"></canvas>
    </div>

    {{-- ── Daily Breakdown Table ───────────────────────────────────────────── --}}
    <div class="rounded-xl bg-white border border-gray-100 shadow-sm print:shadow-none">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 print:hidden">
            <h3 class="text-sm font-semibold text-gray-700">Daily Breakdown</h3>
            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">
                {{ $rows->count() }} days
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-gray-400">Date</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-gray-400">Issued</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-gray-400">Returns</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-violet-500">Winnings</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-teal-500">Cash</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-blue-500">Commission</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-rose-500">Expenses</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-gray-500">Net Profit</th>
                        <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-orange-500">Outstanding</th>
                        <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-gray-400 print:hidden">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($rows as $r)
                        @if($r['records'] > 0 || $r['gross_commission'] > 0 || $r['total_expenses'] > 0)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}
                                <span class="ml-1 text-gray-300">({{ $r['records'] }})</span>
                            </td>
                            <td class="px-4 py-2 text-right text-gray-600">{{ number_format($r['issued_val'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-orange-500">{{ number_format($r['returns_val'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-violet-600">{{ number_format($r['total_winning'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-teal-600">{{ number_format($r['cash_collected'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-blue-600 font-medium">{{ number_format($r['gross_commission'], 2) }}</td>
                            <td class="px-4 py-2 text-right text-rose-600">{{ number_format($r['total_expenses'], 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold
                                       {{ $r['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ number_format($r['net_profit'], 2) }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $r['outstanding'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                {{ number_format($r['outstanding'], 0) }}
                            </td>
                            <td class="px-4 py-2 text-center print:hidden">
                                <a href="{{ route('reports.pdf.daily-sales', ['date' => $r['date']]) }}"
                                   target="_blank"
                                   class="rounded bg-red-50 px-2 py-0.5 text-xs text-red-600 hover:bg-red-100">PDF</a>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-blue-50/60 font-bold text-xs">
                        <td class="px-4 py-3 text-blue-700">TOTALS ({{ $rows->where('records', '>', 0)->count() }} days)</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($totals['issued_val'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-orange-600">{{ number_format($totals['returns_val'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-violet-700">{{ number_format($totals['total_winning'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-teal-700">{{ number_format($totals['cash_collected'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-blue-700">{{ number_format($totals['gross_commission'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-rose-700">{{ number_format($totals['total_expenses'], 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $totals['net_profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ number_format($totals['net_profit'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-orange-700">{{ number_format($totals['outstanding'], 0) }}</td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    const labels    = @json($chartLabels->values());
    const commData  = @json($chartRows->pluck('gross_commission')->values());
    const expData   = @json($chartRows->pluck('total_expenses')->values());
    const profData  = commData.map((c, i) => c - (expData[i] || 0));

    new Chart(document.getElementById('reportChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Commission', data: commData, backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4, order: 2 },
                { label: 'Expenses',   data: expData,  backgroundColor: 'rgba(244,63,94,0.6)',  borderRadius: 4, order: 2 },
                {
                    label: 'Net Profit', data: profData, type: 'line',
                    borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)',
                    borderWidth: 2, pointBackgroundColor: '#10b981', pointRadius: 3,
                    fill: true, tension: 0.35, order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: c => ` Rs.${c.parsed.y.toLocaleString('en-LK', {minimumFractionDigits:2})}` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10 }, callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k' },
                },
            },
        },
    });
    </script>
    @endpush

</x-layouts.app>
