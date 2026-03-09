<x-layouts.app title="Board Transaction Ledger">

{{-- ══════════════════════════════════════════════════════════════════════════
     HEADER
════════════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Board Transaction Ledger</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            W.R Soysa · NLB–DLB Lottery Agent — all ticket receipts &amp; payments with the board
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('board-settlement.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-blue-300 text-blue-700
                  text-sm font-semibold px-4 py-2.5 hover:bg-blue-50 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Settlement Entry
        </a>
        <a href="{{ route('board-transactions.create') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700
                  text-white text-sm font-semibold px-4 py-2.5 shadow-sm transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Manual Entry
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SUMMARY CARDS
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-1">Tickets Received</p>
        <p class="text-2xl font-extrabold text-blue-800">{{ number_format($monthTicketQty) }}</p>
        <p class="text-xs text-blue-400 mt-1">{{ now()->format('F Y') }} · units</p>
    </div>

    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <p class="text-xs font-medium text-indigo-600 uppercase tracking-wide mb-1">Ticket Value (Month)</p>
        <p class="text-2xl font-extrabold text-indigo-800">{{ number_format($monthReceived, 2) }}</p>
        <p class="text-xs text-indigo-400 mt-1">Debit to board</p>
    </div>

    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
        <p class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Total Paid (Month)</p>
        <p class="text-2xl font-extrabold text-green-800">{{ number_format($monthPaid, 2) }}</p>
        <p class="text-xs text-green-400 mt-1">Winning + Cash + Bank</p>
    </div>

    <div class="rounded-xl border-2 p-4
        {{ $outstanding > 0 ? 'border-red-300 bg-red-50' : ($outstanding < 0 ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50') }}">
        <p class="text-xs font-medium uppercase tracking-wide mb-1
            {{ $outstanding > 0 ? 'text-red-600' : ($outstanding < 0 ? 'text-green-600' : 'text-gray-500') }}">
            Outstanding Balance
        </p>
        <p class="text-2xl font-extrabold
            {{ $outstanding > 0 ? 'text-red-800' : ($outstanding < 0 ? 'text-green-800' : 'text-gray-600') }}">
            {{ number_format(abs($outstanding), 2) }}
        </p>
        <p class="text-xs mt-1
            {{ $outstanding > 0 ? 'text-red-400' : ($outstanding < 0 ? 'text-green-400' : 'text-gray-400') }}">
            {{ $outstanding > 0 ? 'Owed to board' : ($outstanding < 0 ? 'Board owes agent' : 'Fully settled') }}
        </p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('board-transactions.index') }}"
          class="flex flex-wrap gap-3 items-end">

        {{-- Quick period shortcuts --}}
        <div class="flex gap-2">
            <a href="{{ route('board-transactions.index', array_merge(request()->except('period','from','to','page'), ['period'=>'week'])) }}"
               class="rounded-lg border px-3 py-2 text-xs font-semibold transition-colors
                      {{ request('period') === 'week' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                This Week
            </a>
            <a href="{{ route('board-transactions.index', array_merge(request()->except('period','from','to','page'), ['period'=>'month'])) }}"
               class="rounded-lg border px-3 py-2 text-xs font-semibold transition-colors
                      {{ request('period') === 'month' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                This Month
            </a>
        </div>

        <div class="h-8 border-l border-gray-200 self-center"></div>

        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">From</label>
            <input type="date" name="from" value="{{ request('from', $from) }}" class="erp-input text-xs w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">To</label>
            <input type="date" name="to" value="{{ request('to', $to) }}" class="erp-input text-xs w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 font-medium">Description</label>
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
     LEDGER TABLE
     Columns match the image: No | Date 01 | Date 02 | Description
     | Amt | Value | NLB Win | DLB Win | Cash | Bank | Cr.01 | Cr.BL
════════════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">

    {{-- Table caption bar --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
        <div>
            <span class="text-sm font-bold text-gray-800">W.R Soysa — Normal Tickets</span>
            @if($transactions->total())
                <span class="ml-2 text-xs text-gray-400">{{ $transactions->total() }} records</span>
            @endif
        </div>
        @if($outstanding != 0)
        <span class="text-sm font-semibold px-3 py-1 rounded-full
            {{ $outstanding > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            {{ $outstanding > 0 ? 'Outstanding' : 'Excess' }}: {{ number_format(abs($outstanding), 2) }}
        </span>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead class="bg-slate-800 text-white">
                <tr>
                    <th class="px-2.5 py-3 text-left font-semibold w-8">No</th>
                    <th class="px-2.5 py-3 text-left font-semibold whitespace-nowrap">Date 01</th>
                    <th class="px-2.5 py-3 text-left font-semibold whitespace-nowrap">Date 02</th>
                    <th class="px-2.5 py-3 text-left font-semibold">Description</th>
                    <th class="px-2.5 py-3 text-right font-semibold">Amt</th>
                    <th class="px-2.5 py-3 text-right font-semibold">Total Value</th>
                    <th class="px-2.5 py-3 text-right font-semibold bg-blue-900/50">NLB Win</th>
                    <th class="px-2.5 py-3 text-right font-semibold bg-purple-900/50">DLB Win</th>
                    <th class="px-2.5 py-3 text-right font-semibold bg-green-900/50">Cash</th>
                    <th class="px-2.5 py-3 text-right font-semibold">Bank</th>
                    <th class="px-2.5 py-3 text-right font-semibold">Cr.01</th>
                    <th class="px-2.5 py-3 text-right font-semibold bg-yellow-600/70 whitespace-nowrap">Cr.BL</th>
                    <th class="px-2.5 py-3 text-center font-semibold w-10">×</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $idx => $tx)
                @php
                    $isGet    = $tx->description === 'get_tickets';
                    $isPaid   = $tx->description === 'paid_bill';
                    $isCredit = $tx->description === 'credit';
                    $isAuto   = $tx->board_settlement_id !== null;
                @endphp
                <tr class="hover:bg-gray-50 transition-colors
                    {{ $isPaid   ? 'bg-blue-50/30' : '' }}
                    {{ $isCredit ? 'bg-amber-50/30' : '' }}">

                    <td class="px-2.5 py-2 text-gray-400">{{ $transactions->firstItem() + $idx }}</td>

                    <td class="px-2.5 py-2 font-medium text-gray-800 whitespace-nowrap">
                        {{ $tx->date->format('Y-m-d') }}
                    </td>

                    <td class="px-2.5 py-2 text-gray-500 whitespace-nowrap">
                        {{ $tx->date_02?->format('Y-m-d') ?? '' }}
                    </td>

                    <td class="px-2.5 py-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex rounded-md px-2 py-0.5 font-semibold whitespace-nowrap
                                {{ $isGet    ? 'bg-blue-100 text-blue-700'
                                 : ($isPaid   ? 'bg-green-100 text-green-700'
                                 : 'bg-amber-100 text-amber-700') }}">
                                {{ $tx->description_label }}
                            </span>
                            @if($isAuto)
                            <span title="Auto-posted from Board Settlement"
                                  class="text-gray-400 cursor-help">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            @endif
                        </div>
                        @if($tx->notes && !$isAuto)
                            <p class="text-gray-400 mt-0.5 leading-tight">{{ $tx->notes }}</p>
                        @endif
                    </td>

                    {{-- Amt (ticket qty) --}}
                    <td class="px-2.5 py-2 text-right text-gray-700">
                        {{ $tx->ticket_qty ? number_format($tx->ticket_qty) : '' }}
                    </td>

                    {{-- Total Ticket Value / Credit Amount --}}
                    <td class="px-2.5 py-2 text-right font-semibold text-gray-800">
                        @if($isGet)
                            {{ number_format($tx->ticket_value, 2) }}
                        @elseif($isPaid)
                            <span class="text-gray-300">0</span>
                        @else
                            {{ $tx->credit_amount > 0 ? number_format($tx->credit_amount, 2) : '' }}
                        @endif
                    </td>

                    {{-- NLB Winning --}}
                    <td class="px-2.5 py-2 text-right text-blue-700 font-medium bg-blue-50/40">
                        {{ $tx->nlb_winning > 0 ? number_format($tx->nlb_winning, 2) : '' }}
                    </td>

                    {{-- DLB Winning --}}
                    <td class="px-2.5 py-2 text-right text-purple-700 font-medium bg-purple-50/40">
                        {{ $tx->dlb_winning > 0 ? number_format($tx->dlb_winning, 2) : '' }}
                    </td>

                    {{-- Cash --}}
                    <td class="px-2.5 py-2 text-right text-green-700 font-medium bg-green-50/40">
                        {{ $tx->cash_amount > 0 ? number_format($tx->cash_amount, 2) : '' }}
                    </td>

                    {{-- Bank --}}
                    <td class="px-2.5 py-2 text-right text-slate-600">
                        {{ $tx->bank_deposits > 0 ? number_format($tx->bank_deposits, 2) : '' }}
                    </td>

                    {{-- Cr.01 (net effect on balance for this row) --}}
                    <td class="px-2.5 py-2 text-right font-semibold
                        {{ $tx->cr_amount >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        {{ number_format($tx->cr_amount, 2) }}
                    </td>

                    {{-- Cr.BL (running balance — highlighted) --}}
                    <td class="px-2.5 py-2 text-right font-bold bg-yellow-50/70
                        {{ $tx->balance > 0 ? 'text-red-700' : ($tx->balance < 0 ? 'text-green-700' : 'text-gray-400') }}">
                        {{ number_format($tx->balance, 2) }}
                    </td>

                    {{-- Delete (disabled for auto-posted rows — delete via Settlement) --}}
                    <td class="px-2.5 py-2 text-center">
                        @if($isAuto)
                            <a href="{{ route('board-settlement.index', ['date' => $tx->date->toDateString()]) }}"
                               title="Edit via Settlement"
                               class="text-blue-400 hover:text-blue-600 transition-colors">
                                <svg class="h-3.5 w-3.5 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        @else
                            <form method="POST"
                                  action="{{ route('board-transactions.destroy', $tx) }}"
                                  onsubmit="return confirm('Delete this transaction? Running balances will recalculate.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete"
                                        class="text-red-400 hover:text-red-600 transition-colors">
                                    <svg class="h-3.5 w-3.5 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="py-16 text-center text-gray-400">
                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No transactions found.
                        <a href="{{ route('board-settlement.index') }}" class="text-blue-600 hover:underline">Save a settlement</a>
                        or
                        <a href="{{ route('board-transactions.create') }}" class="text-blue-600 hover:underline">add a manual entry.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>

            {{-- ── Page subtotals + Outstanding row ────────────────────────── --}}
            @if($transactions->isNotEmpty())
            <tfoot>
                {{-- Page subtotals --}}
                <tr class="border-t border-gray-200 bg-gray-100 font-semibold text-gray-700 text-xs">
                    <td colspan="4" class="px-2.5 py-2.5 text-gray-500 font-medium">Page Sub-Total</td>
                    <td class="px-2.5 py-2.5 text-right">
                        {{ number_format($transactions->sum('ticket_qty')) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right">
                        {{ number_format($transactions->sum('ticket_value'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right text-blue-700 bg-blue-50/40">
                        {{ number_format($transactions->sum('nlb_winning'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right text-purple-700 bg-purple-50/40">
                        {{ number_format($transactions->sum('dlb_winning'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right text-green-700 bg-green-50/40">
                        {{ number_format($transactions->sum('cash_amount'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right">
                        {{ number_format($transactions->sum('bank_deposits'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 text-right">
                        {{ number_format($transactions->sum('cr_amount'), 2) }}
                    </td>
                    <td class="px-2.5 py-2.5 bg-yellow-50/70"></td>
                    <td></td>
                </tr>

                {{-- Total Outstanding Due — the key summary row --}}
                <tr class="border-t-2 border-gray-800 bg-gray-900 text-white">
                    <td colspan="10" class="px-2.5 py-3 text-sm font-bold text-gray-200">
                        Total Outstanding Due to Board
                    </td>
                    <td colspan="2" class="px-2.5 py-3 text-right text-lg font-extrabold
                        {{ $outstanding > 0 ? 'text-red-300' : ($outstanding < 0 ? 'text-green-300' : 'text-gray-400') }}">
                        {{ number_format(abs($outstanding), 2) }}
                        @if($outstanding > 0)
                            <span class="text-xs font-normal text-red-400 ml-1">OWED</span>
                        @elseif($outstanding < 0)
                            <span class="text-xs font-normal text-green-400 ml-1">EXCESS</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
            of {{ $transactions->total() }} entries
        </p>
        {{ $transactions->links() }}
    </div>
    @endif

    {{-- Legend --}}
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex flex-wrap gap-4 text-xs text-gray-500">
        <div class="flex items-center gap-1.5">
            <svg class="h-3 w-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
            </svg>
            Auto-posted from Board Settlement
        </div>
        <span>· Cr.01 = net effect on balance per row</span>
        <span>· Cr.BL = cumulative running balance</span>
        <span>· Red balance = owed to board</span>
        <span>· Green balance = excess / overpaid</span>
    </div>
</div>

</x-layouts.app>
