<x-layouts.app title="Assistant Collections">

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Assistant Collections</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Handed-over ticket distributions and amounts due</p>
        </div>

        {{-- Date range filter --}}
        <form method="GET" action="{{ route('ticket-distribution.assistant-collections') }}"
              class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-medium text-gray-500 dark:text-slate-400 whitespace-nowrap">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800
                              text-gray-800 dark:text-slate-200 text-sm px-3 py-1.5 focus:outline-none
                              focus:ring-2 focus:ring-indigo-500/50">
            </div>
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-medium text-gray-500 dark:text-slate-400 whitespace-nowrap">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800
                              text-gray-800 dark:text-slate-200 text-sm px-3 py-1.5 focus:outline-none
                              focus:ring-2 focus:ring-indigo-500/50">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700
                           text-white text-sm font-semibold px-4 py-1.5 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl bg-slate-800 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-70">Total Records</p>
            <p class="text-3xl font-bold mt-1">{{ $records->count() }}</p>
        </div>
        <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-80">Total Tickets Handed Over</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($grandTotalTickets) }}</p>
        </div>
        <div class="rounded-xl bg-indigo-600 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-80">Total Amount Due</p>
            <p class="text-3xl font-bold mt-1">Rs. {{ number_format($grandTotalAmount) }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-slate-200 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Assistant Name</th>
                        <th class="px-5 py-3 text-right">Tickets Handed Over</th>
                        <th class="px-5 py-3 text-right">Rate</th>
                        <th class="px-5 py-3 text-right">Total Amount Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">

                        {{-- Date --}}
                        <td class="px-5 py-3 text-gray-600 dark:text-slate-300 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                        </td>

                        {{-- Assistant Name --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full
                                            bg-indigo-100 dark:bg-indigo-500/20
                                            text-indigo-700 dark:text-indigo-300
                                            text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($record->assistant->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800 dark:text-slate-200">
                                    {{ $record->assistant->name ?? '—' }}
                                </span>
                            </div>
                        </td>

                        {{-- Tickets Handed Over --}}
                        <td class="px-5 py-3 text-right">
                            <span class="inline-flex items-center justify-center rounded-full
                                         bg-emerald-50 dark:bg-emerald-500/10
                                         border border-emerald-100 dark:border-emerald-500/20
                                         text-emerald-700 dark:text-emerald-400
                                         text-xs font-semibold px-2.5 py-0.5 min-w-[3rem]">
                                {{ number_format($record->total_tickets) }}
                            </span>
                        </td>

                        {{-- Rate --}}
                        <td class="px-5 py-3 text-right text-gray-500 dark:text-slate-400 text-xs">
                            Rs. 35
                        </td>

                        {{-- Total Amount Due --}}
                        <td class="px-5 py-3 text-right">
                            <span class="font-semibold text-gray-800 dark:text-slate-200">
                                Rs. {{ number_format($record->amount_due) }}
                            </span>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-slate-500">
                                <svg class="h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                                </svg>
                                <p class="text-sm font-medium">No handed-over records found</p>
                                <p class="text-xs">Try adjusting the date range filter above.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($records->isNotEmpty())
                <tfoot class="bg-slate-50 dark:bg-slate-800/60 border-t-2 border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="2" class="px-5 py-3 text-xs font-bold text-gray-600 dark:text-slate-300 uppercase tracking-wide">
                            Totals
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-gray-800 dark:text-slate-200">
                            {{ number_format($grandTotalTickets) }}
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-400 dark:text-slate-500">
                            Rs. 35
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-indigo-600 dark:text-indigo-400">
                            Rs. {{ number_format($grandTotalAmount) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

</x-layouts.app>
