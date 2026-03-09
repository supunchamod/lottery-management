<x-layouts.app title="Lottery Stock">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Lottery Stock</h2>
            <p class="text-sm text-gray-500">Daily ticket issuance records per assistant.</p>
        </div>
        <a href="{{ route('stock.create') }}"
           class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Issue Stock
        </a>
    </div>

    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Agent</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Lottery</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-blue-500">Board</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Qty Issued</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($stocks ?? [] as $s)
                        <tr>
                            <td class="px-5 py-3 text-gray-600">{{ $s->date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $s->agent->name }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $s->lottery->name }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold
                                             {{ $s->lottery->board === 'NLB' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $s->lottery->board }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-700">{{ number_format($s->qty_issued) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-800">
                                Rs.{{ number_format($s->qty_issued * $s->lottery->unit_price, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">No stock records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
