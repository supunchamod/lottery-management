<x-layouts.app title="Reports">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Reports</h2>
        <p class="text-sm text-gray-500">Generate profit & loss, ledger and commission reports.</p>
    </div>

    {{-- Date Range Selector --}}
    <form method="GET" action="{{ route('reports.index') }}" class="mb-6">
        <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->toDateString()) }}"
                       class="erp-input !w-40">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to', now()->toDateString()) }}"
                       class="erp-input !w-40">
            </div>
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Generate
            </button>
        </div>
    </form>

    @if(!empty($summary))
    {{-- Summary totals --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @php $totals = $summary['totals']; @endphp
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Gross Commission</p>
            <p class="text-xl font-bold text-blue-600">Rs.{{ number_format($totals['gross_commission'], 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Total Expenses</p>
            <p class="text-xl font-bold text-rose-600">Rs.{{ number_format($totals['total_expenses'], 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Net Profit</p>
            <p class="text-xl font-bold {{ $totals['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                Rs.{{ number_format($totals['net_profit'], 2) }}
            </p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Cash Collected</p>
            <p class="text-xl font-bold text-gray-800">Rs.{{ number_format($totals['cash_collected'], 2) }}</p>
        </div>
    </div>

    {{-- Daily Breakdown --}}
    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Daily P&L Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-blue-500">Commission</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-rose-500">Expenses</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Net Profit</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Issued</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Cash</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($summary['days'] as $d)
                        <tr>
                            <td class="px-5 py-2.5 text-gray-600">{{ \Carbon\Carbon::parse($d['date'])->format('d M Y') }}</td>
                            <td class="px-5 py-2.5 text-right text-blue-600">Rs.{{ number_format($d['gross_commission'], 2) }}</td>
                            <td class="px-5 py-2.5 text-right text-rose-600">Rs.{{ number_format($d['total_expenses'], 2) }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold {{ $d['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rs.{{ number_format($d['net_profit'], 2) }}
                            </td>
                            <td class="px-5 py-2.5 text-right text-gray-600">Rs.{{ number_format($d['issued_val'], 2) }}</td>
                            <td class="px-5 py-2.5 text-right text-gray-600">Rs.{{ number_format($d['cash_collected'], 2) }}</td>
                            <td class="px-5 py-2.5 text-right {{ $d['outstanding'] > 0 ? 'text-red-500' : 'text-gray-500' }}">
                                Rs.{{ number_format($d['outstanding'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</x-layouts.app>
