<x-layouts.app title="Board Transaction Ledger">

{{-- ══════════════════════════════════════════════════════════════════════════
     HEADER ROW
════════════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Board Transaction Ledger</h2>
        <p class="text-sm text-gray-500 mt-0.5">W.R Soysa · NLB–DLB Lottery Agent — running balance with board</p>
    </div>
    <a href="{{ route('board-transactions.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700
              text-white text-sm font-semibold px-4 py-2.5 shadow-sm transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Entry
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SUMMARY CARDS
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Tickets Received (this month) --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-1">Tickets Received (Month)</p>
        <p class="text-2xl font-extrabold text-blue-800">{{ number_format($monthTicketQty) }}</p>
        <p class="text-xs text-blue-500 mt-1">{{ now()->format('F Y') }}</p>
    </div>

    {{-- Ticket Value (this month) --}}
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <p class="text-xs font-medium text-indigo-600 uppercase tracking-wide mb-1">Ticket Value (Month)</p>
        <p class="text-2xl font-extrabold text-indigo-800">{{ number_format($monthReceived, 2) }}</p>
        <p class="text-xs text-indigo-500 mt-1">Total debit to board</p>
    </div>

    {{-- Total Paid (this month) --}}
    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
        <p class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Total Paid (Month)</p>
        <p class="text-2xl font-extrabold text-green-800">{{ number_format($monthPaid, 2) }}</p>
        <p class="text-xs text-green-500 mt-1">Winning + Cash + Bank</p>
    </div>

    {{-- Outstanding Balance --}}
    <div class="rounded-xl border-2 p-4
        {{ $outstanding > 0 ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50' }}">
        <p class="text-xs font-medium uppercase tracking-wide mb-1
            {{ $outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">
            Outstanding Balance
        </p>
        <p class="text-2xl font-extrabold {{ $outstanding > 0 ? 'text-red-800' : 'text-green-800' }}">
            {{ number_format(abs($outstanding), 2) }}
        </p>
        <p class="text-xs mt-1 {{ $outstanding > 0 ? 'text-red-500' : 'text-green-500' }}">
            {{ $outstanding > 0 ? 'Amount owed to board' : ($outstanding < 0 ? 'Board owes agent' : 'Fully settled') }}
        </p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('board-transactions.index') }}"
          class="flex flex-wrap gap-3 items-end">

        {{-- Period shortcuts --}}
        <div class="flex gap-2">
            <a href="{{ route('board-transactions.index', array_merge(request()->except('period','from','to'), ['period'=>'week'])) }}"
               class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors
                      {{ request('period') === 'week' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                This Week
            </a>
            <a href="{{ route('board-transactions.index', array_merge(request()->except('period','from','to'), ['period'=>'month'])) }}"
               class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors
                      {{ request('period') === 'month' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                This Month
            </a>
        </div>

        <div class="h-8 border-l border-gray-200"></div>

        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">From</label>
            <input type="date" name="from" value="{{ request('from', $from) }}" class="erp-input text-xs w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">To</label>
            <input type="date" name="to" value="{{ request('to', $to) }}" class="erp-input text-xs w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">Type</label>
            <select name="type" class="erp-input text-xs w-36">
                <option value="">All Types</option>
                <option value="get_tickets" {{ request('type') === 'get_tickets' ? 'selected' : '' }}>Get Tickets</option>
                <option value="paid_bill"   {{ request('type') === 'paid_bill'   ? 'selected' : '' }}>Paid Bill</option>
                <option value="credit"      {{ request('type') === 'credit'      ? 'selected' : '' }}>Credit</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-blue-600 text-white text-xs font-semibold px-3 py-2 hover:bg-blue-700">
                Filter
            </button>
            <a href="{{ route('board-transactions.index') }}"
               class="rounded-lg border border-gray-300 text-gray-600 text-xs font-medium px-3 py-2 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     LEDGER TABLE — matches image layout exactly
════════════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">

    {{-- Table header info --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
        <div>
            <span class="text-sm font-bold text-gray-800">W.R Soysa — Normal Tickets</span>
            @if($transactions->total())
                <span class="ml-3 text-xs text-gray-400">{{ $transactions->total() }} records</span>
            @endif
        </div>
        @if($outstanding != 0)
        <div class="text-sm font-semibold {{ $outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ $outstanding > 0 ? 'Outstanding' : 'Excess' }}: {{ number_format(abs($outstanding), 2) }}
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            {{-- Exact columns from image --}}
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-2.5 text-left font-semibold text-gray-600 w-8">No</th>
                    <th class="px-3 py-2.5 text-left font-semibold text-gray-600">Date 01</th>
                    <th class="px-3 py-2.5 text-left font-semibold text-gray-600">Date 02</th>
                    <th class="px-3 py-2.5 text-left font-semibold text-gray-600 w-28">Description</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Amt</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Value</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Winning</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Cash</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Bank</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600">Cr.01</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-gray-600 bg-yellow-50">Cr.BL</th>
                    <th class="px-3 py-2.5 text-center font-semibold text-gray-500 w-10">Del</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $idx => $tx)
                @php
                    $isGetTickets = $tx->description === 'get_tickets';
                    $isPaidBill   = $tx->description === 'paid_bill';
                    $isCredit     = $tx->description === 'credit';
                    $rowBg        = $isPaidBill ? 'bg-blue-50/40' : ($isCredit ? 'bg-amber-50/40' : '');
                @endphp
                <tr class="hover:bg-gray-50 transition-colors {{ $rowBg }}">
                    <td class="px-3 py-2 text-gray-400">{{ $transactions->firstItem() + $idx }}</td>

                    {{-- Date 01 --}}
                    <td class="px-3 py-2 font-medium text-gray-800 whitespace-nowrap">
                        {{ $tx->date->format('Y-m-d') }}
                    </td>

                    {{-- Date 02 --}}
                    <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                        {{ $tx->date_02?->format('Y-m-d') ?? '' }}
                    </td>

                    {{-- Description badge --}}
                    <td class="px-3 py-2">
                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold
                            {{ $isGetTickets ? 'bg-blue-100 text-blue-700'
                             : ($isPaidBill   ? 'bg-green-100 text-green-700'
                             : 'bg-amber-100 text-amber-700') }}">
                            {{ $tx->description_label }}
                        </span>
                    </td>

                    {{-- Amt (ticket qty) --}}
                    <td class="px-3 py-2 text-right text-gray-700">
                        {{ $tx->ticket_qty ? number_format($tx->ticket_qty) : '' }}
                    </td>

                    {{-- Value (ticket value) --}}
                    <td class="px-3 py-2 text-right font-medium text-gray-800">
                        @if($isGetTickets)
                            {{ number_format($tx->ticket_value, 2) }}
                        @elseif($isPaidBill)
                            <span class="text-gray-400">0</span>
                        @else
                            {{ $tx->credit_amount > 0 ? number_format($tx->credit_amount, 2) : '' }}
                        @endif
                    </td>

                    {{-- Winning --}}
                    <td class="px-3 py-2 text-right text-indigo-700 font-medium">
                        {{ $tx->winning_amount > 0 ? number_format($tx->winning_amount, 2) : '' }}
                    </td>

                    {{-- Cash --}}
                    <td class="px-3 py-2 text-right text-green-700 font-medium">
                        {{ $tx->cash_amount > 0 ? number_format($tx->cash_amount, 2) : '' }}
                    </td>

                    {{-- Bank --}}
                    <td class="px-3 py-2 text-right text-blue-600">
                        {{ $tx->bank_deposits > 0 ? number_format($tx->bank_deposits, 2) : '' }}
                    </td>

                    {{-- Cr.01 (net effect) --}}
                    <td class="px-3 py-2 text-right font-semibold
                        {{ $tx->cr_amount >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        {{ number_format($tx->cr_amount, 2) }}
                    </td>

                    {{-- Cr.BL (running balance) --}}
                    <td class="px-3 py-2 text-right font-bold bg-yellow-50/60
                        {{ $tx->balance > 0 ? 'text-red-700' : ($tx->balance < 0 ? 'text-green-700' : 'text-gray-500') }}">
                        {{ number_format($tx->balance, 2) }}
                    </td>

                    {{-- Delete --}}
                    <td class="px-3 py-2 text-center">
                        <form method="POST"
                              action="{{ route('board-transactions.destroy', $tx) }}"
                              onsubmit="return confirm('Delete this transaction? Running balances will be recalculated.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors" title="Delete">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="py-12 text-center text-gray-400">
                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No transactions found. <a href="{{ route('board-transactions.create') }}" class="text-blue-600 hover:underline">Add the first entry.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>

            {{-- Page totals footer --}}
            @if($transactions->isNotEmpty())
            <tfoot class="border-t-2 border-gray-300 bg-gray-100">
                <tr class="font-bold text-xs text-gray-700">
                    <td colspan="4" class="px-3 py-2.5 text-gray-500">Page Total</td>
                    <td class="px-3 py-2.5 text-right">
                        {{ number_format($transactions->sum('ticket_qty')) }}
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        {{ number_format($transactions->sum('ticket_value'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right text-indigo-700">
                        {{ number_format($transactions->sum('winning_amount'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right text-green-700">
                        {{ number_format($transactions->sum('cash_amount'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right text-blue-600">
                        {{ number_format($transactions->sum('bank_deposits'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        {{ number_format($transactions->sum('cr_amount'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right bg-yellow-100 font-extrabold
                        {{ $transactions->last()?->balance > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($transactions->last()?->balance ?? 0, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
