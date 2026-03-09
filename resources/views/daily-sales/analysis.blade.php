<x-layouts.app title="Sales Analysis">

{{-- ── Controls ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-end gap-3">
    <a href="{{ route('daily-sales.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        ← Daily Grid
    </a>

    <form method="GET" action="{{ route('daily-sales.analysis') }}" class="flex flex-wrap items-end gap-3">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Assistant</label>
            <select name="assistant_id" onchange="this.form.submit()"
                    class="erp-input text-sm h-9 pr-8">
                @foreach($assistants as $a)
                    <option value="{{ $a->id }}" @selected($a->id == $assistantId)>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Period</label>
            <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                @foreach(['today' => 'Today', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'overall' => 'Overall'] as $val => $label)
                    <button type="submit" name="period" value="{{ $val }}"
                            class="px-4 py-2 font-medium transition
                                   {{ $period === $val
                                      ? 'bg-blue-600 text-white'
                                      : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </form>
</div>

{{-- ── Assistant Info + Period Label ────────────────────────────────────── --}}
<div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-5 py-3 flex flex-wrap items-center gap-5">
    <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white font-bold text-lg">
            {{ strtoupper(substr($assistant?->name ?? '?', 0, 1)) }}
        </div>
        <div>
            <p class="font-bold text-gray-900">{{ $assistant?->name ?? '—' }}</p>
            <p class="text-xs text-gray-500">{{ $assistant?->phone }}</p>
        </div>
    </div>
    <div class="h-8 w-px bg-blue-200 hidden sm:block"></div>
    <div>
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Period</p>
        <p class="font-semibold text-gray-800 capitalize">{{ ucfirst($period) }}</p>
    </div>
    <div>
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Records</p>
        <p class="font-semibold text-gray-800">{{ $stats['recordCount'] }}</p>
    </div>
    <div class="ml-auto text-right">
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Current Balance</p>
        <p class="text-xl font-bold {{ ($assistant?->current_balance ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
            Rs. {{ number_format(abs($assistant?->current_balance ?? 0), 2) }}
        </p>
    </div>
</div>

{{-- ── Stats Grid ────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
    @php
        $statCards = [
            ['label'=>'Total Value',      'value'=>$stats['totalValue'],       'color'=>'bg-gray-900 text-white'],
            ['label'=>'Total Cash',       'value'=>$stats['totalCash'],        'color'=>'bg-emerald-600 text-white'],
            ['label'=>'Total Winning',    'value'=>$stats['totalWinning'],     'color'=>'bg-violet-600 text-white'],
            ['label'=>'Total C+W',        'value'=>$stats['totalCW'],          'color'=>'bg-blue-600 text-white'],
            ['label'=>'Outstanding',      'value'=>$stats['totalOutstanding'], 'color'=>'bg-red-600 text-white'],
        ];
    @endphp
    @foreach($statCards as $c)
        <div class="rounded-xl {{ $c['color'] }} px-4 py-3">
            <p class="text-xs opacity-70 mb-0.5">{{ $c['label'] }}</p>
            <p class="text-lg font-bold">Rs. {{ number_format($c['value'], 0) }}</p>
        </div>
    @endforeach
</div>

{{-- ── Chart ──────────────────────────────────────────────────────────────── --}}
@if(count($chartLabels) > 0)
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Performance Trend</h3>
        <div class="flex gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-gray-800"></span>Value</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-emerald-500"></span>Cash</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-red-500"></span>Balance</span>
        </div>
    </div>
    <canvas id="perfChart" style="max-height:260px;"></canvas>
</div>
@endif

{{-- ── Records Table ─────────────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-5 py-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Detailed Records</h3>
        <span class="text-xs text-gray-400">{{ $records->count() }} records</span>
    </div>

    @if($records->isEmpty())
        <div class="py-14 text-center text-sm text-gray-400">No records found for this period.</div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Date</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-gray-500">Qty</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-yellow-600">Value</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-emerald-600">Cash</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-violet-600">NLB Win</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-violet-600">DLB Win</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-purple-600">TW</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-cyan-600">C+W</th>
                    <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($records->sortByDesc('date') as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 font-medium text-gray-700">{{ $r->date->format('d M Y') }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format($r->tickets_issued_qty) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-yellow-700">{{ number_format($r->value, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($r->cash, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($r->nlb_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($r->dlb_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-purple-700">{{ number_format($r->total_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-cyan-700">{{ number_format($r->cw, 0) }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if($r->balance > 0)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">Outstanding</span>
                        @elseif($r->balance < 0)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Credit</span>
                        @else
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">Balanced</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-gray-500 italic">{{ $r->remarks ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-300 font-bold bg-gray-50">
                <tr>
                    <td class="px-4 py-2.5 text-gray-700">Totals</td>
                    <td class="px-3 py-2.5 text-right text-gray-900">{{ number_format($records->sum('tickets_issued_qty')) }}</td>
                    <td class="px-3 py-2.5 text-right text-yellow-700">{{ number_format($records->sum('value'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($records->sum('cash'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($records->sum('nlb_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($records->sum('dlb_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-purple-700">{{ number_format($records->sum('total_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-cyan-700">{{ number_format($records->sum('cw'), 0) }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @php $tb = $records->sum('balance'); @endphp
                        <span class="{{ $tb > 0 ? 'text-red-700' : ($tb < 0 ? 'text-amber-700' : 'text-green-700') }}">
                            Rs. {{ number_format(abs($tb), 0) }}
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@if(count($chartLabels) > 0)
@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('perfChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Value',
                    data: {!! json_encode($chartValue) !!},
                    backgroundColor: 'rgba(17,24,39,0.75)',
                    borderRadius: 4,
                    order: 3,
                },
                {
                    label: 'Cash',
                    data: {!! json_encode($chartCash) !!},
                    backgroundColor: 'rgba(16,185,129,0.8)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Balance',
                    data: {!! json_encode($chartBalance) !!},
                    type: 'line',
                    borderColor: 'rgba(239,68,68,0.9)',
                    backgroundColor: 'rgba(239,68,68,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.3,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
@endpush
@endif

</x-layouts.app>
