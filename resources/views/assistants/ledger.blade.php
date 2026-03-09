<x-layouts.app title="Ledger — {{ $assistant->name }}">

<div class="mb-5 flex items-center justify-between">
    <a href="{{ route('assistants.index') }}"
       class="text-sm text-blue-600 hover:underline">← Back to Assistants</a>

    <a href="{{ route('reports.pdf.ledger', $assistant) }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Export PDF
    </a>
</div>

{{-- Assistant summary card --}}
<div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4">
        <p class="text-xs font-medium text-blue-500 uppercase tracking-wide mb-1">Assistant</p>
        <p class="text-lg font-bold text-gray-900">{{ $assistant->name }}</p>
        <p class="text-sm text-gray-500">{{ $assistant->phone }}</p>
    </div>
    <div class="rounded-xl border border-{{ $assistant->current_balance > 0 ? 'red' : 'emerald' }}-100
                bg-{{ $assistant->current_balance > 0 ? 'red' : 'emerald' }}-50 px-5 py-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Current Balance</p>
        <p class="text-2xl font-bold {{ $assistant->current_balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
            Rs. {{ number_format(abs($assistant->current_balance), 2) }}
        </p>
        <p class="text-xs text-gray-400">
            {{ $assistant->current_balance > 0 ? 'Assistant owes agency' : ($assistant->current_balance < 0 ? 'Agency owes assistant' : 'Settled') }}
        </p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Entries</p>
        <p class="text-2xl font-bold text-gray-900">{{ $entries->total() }}</p>
        <p class="text-xs text-gray-400">All time ledger records</p>
    </div>
</div>

{{-- Ledger table --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-gray-700">Ledger Entries</h3>
    </div>

    @if($entries->isEmpty())
        <div class="py-12 text-center text-sm text-gray-400">No ledger entries yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-600">
                                {{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                @if($entry->type === 'debit')
                                    <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">DEBIT</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">CREDIT</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium
                                       {{ $entry->type === 'debit' ? 'text-red-600' : 'text-emerald-600' }}">
                                Rs. {{ number_format($entry->amount, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">
                                Rs. {{ number_format($entry->running_balance, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-3">
            {{ $entries->links() }}
        </div>
    @endif
</div>

</x-layouts.app>
