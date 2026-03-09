<x-layouts.app title="Winnings">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Winning Records</h2>
            <p class="text-sm text-gray-500">NLB & DLB daily winning history.</p>
        </div>
        <a href="{{ route('winnings.create') }}"
           class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Enter Today's Winnings
        </a>
    </div>

    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-blue-500">NLB Total</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-orange-500">DLB Total</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Grand Total</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($winnings ?? [] as $w)
                        <tr>
                            <td class="px-5 py-3 font-medium text-gray-700">{{ $w->date->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right text-blue-600 font-semibold">Rs.{{ number_format($w->nlb_total, 2) }}</td>
                            <td class="px-5 py-3 text-right text-orange-600 font-semibold">Rs.{{ number_format($w->dlb_total, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800">Rs.{{ number_format($w->total_val, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                <a href="{{ route('winnings.create') }}?date={{ $w->date->toDateString() }}"
                                   class="text-xs text-blue-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-gray-400">
                                No winning records yet.
                                <a href="{{ route('winnings.create') }}" class="text-blue-600 hover:underline">Add one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!empty($winnings) && $winnings instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="border-t border-gray-100 px-5 py-3">{{ $winnings->links() }}</div>
        @endif
    </div>

</x-layouts.app>
