<x-layouts.app title="Assistant Performance">

    {{-- ── Header ───────────────────────────────────────────────────────────── --}}
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Assistant Performance</h2>
            <p class="text-sm text-gray-500">Sales totals, collection rates and outstanding credits per agent.</p>
        </div>
        <a href="{{ route('reports.index') }}"
           class="text-sm text-blue-600 hover:text-blue-700">← Back to Reports</a>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('reports.assistants') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-100 shadow-sm p-4">

        @foreach(['today'=>'Today','last7'=>'Last 7 Days','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $key=>$label)
            <a href="{{ route('reports.assistants', ['preset'=>$key]) }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                      {{ ($preset ?? 'month') === $key
                          ? 'bg-blue-600 border-blue-600 text-white'
                          : 'border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600' }}">
                {{ $label }}
            </a>
        @endforeach

        <div class="ml-auto flex items-end gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
                <input type="date" name="from" value="{{ $from }}" class="erp-input !w-36" onchange="this.form.submit()">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
                <input type="date" name="to" value="{{ $to }}" class="erp-input !w-36" onchange="this.form.submit()">
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- ── Performance Table ───────────────────────────────────────────── --}}
        <div class="xl:col-span-2 rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-700">
                    Performance: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                </h3>
                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-600">
                    {{ $assistants->count() }} assistants
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-gray-400">Assistant</th>
                            <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-gray-400">Days</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-gray-400">Issued</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-teal-500">Cash</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-violet-500">Winning</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-orange-500">Returns</th>
                            <th class="px-4 py-2.5 text-right font-semibold uppercase tracking-wide text-gray-500">Balance</th>
                            <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-blue-500">Rate%</th>
                            <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-gray-400">Ledger</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($assistants as $a)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                            {{ strtoupper(substr($a['name'], 0, 2)) }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ $a['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center text-gray-500">{{ $a['sale_days'] }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($a['total_issued'], 0) }}</td>
                                <td class="px-4 py-2.5 text-right text-teal-600">{{ number_format($a['total_cash'], 0) }}</td>
                                <td class="px-4 py-2.5 text-right text-violet-600">{{ number_format($a['total_winning'], 0) }}</td>
                                <td class="px-4 py-2.5 text-right text-orange-500">{{ number_format($a['total_returns'], 0) }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="inline-block rounded px-2 py-0.5 text-xs font-semibold
                                                 {{ $a['period_balance'] > 0 ? 'balance-owes' : ($a['period_balance'] < 0 ? 'balance-credit' : 'balance-settled') }}">
                                        {{ number_format($a['period_balance'], 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    @php $rate = $a['collection_rate']; @endphp
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="font-semibold {{ $rate >= 80 ? 'text-emerald-600' : ($rate >= 50 ? 'text-amber-600' : 'text-red-500') }}">
                                            {{ $rate }}%
                                        </span>
                                        <div class="h-1 w-14 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full transition-all
                                                        {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                                 style="width:{{ min(100,$rate) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <a href="{{ route('reports.pdf.ledger', ['assistant' => $a['id'], 'from' => $from, 'to' => $to]) }}"
                                       target="_blank"
                                       class="rounded bg-red-50 px-2 py-0.5 text-xs text-red-600 hover:bg-red-100">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-sm text-gray-400">
                                    No assistant data for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Right panel: Top debtors + trend chart ──────────────────────── --}}
        <div class="flex flex-col gap-4">

            {{-- Leaderboard: highest outstanding -------------------------------- --}}
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-700">Top Outstanding Credits</h3>
                <div class="space-y-2">
                    @foreach($assistants->sortByDesc('period_balance')->take(6) as $i => $a)
                        @if($a['period_balance'] > 0)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-400">#{{ $i + 1 }}</span>
                                <span class="text-sm font-medium text-gray-700">{{ $a['name'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-red-600">Rs.{{ number_format($a['period_balance'], 0) }}</span>
                        </div>
                        @endif
                    @endforeach
                    @if($assistants->where('period_balance', '>', 0)->isEmpty())
                        <p class="text-xs text-center text-gray-400 py-4">All assistants settled ✓</p>
                    @endif
                </div>
            </div>

            {{-- Balance trend sparkline for top assistant ----------------------- --}}
            @if($topAssistant)
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-5">
                <h3 class="mb-1 text-sm font-semibold text-gray-700">Balance Trend</h3>
                <p class="mb-3 text-xs text-gray-400">{{ $topAssistant->name }} (highest balance)</p>
                <canvas id="trendChart" height="160"></canvas>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    @if($topAssistant)
    const trendLabels = @json($trend->keys()->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')));
    const trendData   = @json($trend->values());

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Balance',
                data: trendData,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,0.08)',
                borderWidth: 2,
                pointRadius: 2,
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` Rs.${c.parsed.y.toLocaleString('en-LK', {minimumFractionDigits:2})}` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                y: {
                    grid: { color: '#f8fafc' },
                    ticks: { font: { size: 9 }, callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k' },
                },
            },
        },
    });
    @endif
    </script>
    @endpush

</x-layouts.app>
