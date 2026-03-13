<x-layouts.app title="Bulk Deposits">

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bulk Deposits</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
            Buffer of multi-day sales entries. Pending records can be edited or distributed.
        </p>
    </div>
    <a href="{{ route('bulk-deposits.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700
              px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors self-start">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Bulk Entry
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 dark:border-emerald-800
            bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 dark:border-red-800
            bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-300">
    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    {{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     TABLE CARD
═══════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/5 overflow-hidden">

    @if($bulkDeposits->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-slate-400 dark:text-slate-500">
        <svg class="h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.25" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-sm font-medium">No bulk deposit records yet.</p>
        <a href="{{ route('bulk-deposits.create') }}"
           class="mt-3 text-sm font-semibold text-indigo-500 hover:text-indigo-600">
            Create the first one →
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="min-width:960px">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/40">
                    <th class="px-4 py-3 text-left   text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">#</th>
                    <th class="px-4 py-3 text-left   text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Assistant</th>
                    <th class="px-4 py-3 text-left   text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date Range</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Days</th>
                    <th class="px-4 py-3 text-right  text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Value</th>
                    <th class="px-4 py-3 text-right  text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cash</th>
                    <th class="px-4 py-3 text-right  text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">C+W</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Auto Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                    <th class="px-4 py-3 text-right  text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($bulkDeposits as $deposit)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">

                    {{-- ID --}}
                    <td class="px-4 py-3.5 font-mono text-xs text-slate-400 dark:text-slate-500">
                        #{{ $deposit->id }}
                    </td>

                    {{-- Assistant + notes tooltip --}}
                    <td class="px-4 py-3.5">
                        <span class="font-semibold text-slate-900 dark:text-white">
                            {{ $deposit->assistant->name ?? '—' }}
                        </span>
                        @if($deposit->notes)
                        <span class="block text-xs text-slate-400 dark:text-slate-500 truncate max-w-[140px]"
                              title="{{ $deposit->notes }}">
                            {{ $deposit->notes }}
                        </span>
                        @endif
                    </td>

                    {{-- Date range --}}
                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                        {{ $deposit->date_from->format('d M Y') }}
                        <span class="text-slate-400 mx-1">→</span>
                        {{ $deposit->date_to->format('d M Y') }}
                    </td>

                    {{-- Days --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center rounded-full
                                     bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5
                                     text-xs font-bold text-slate-600 dark:text-slate-300">
                            {{ $deposit->dayCount() }}
                        </span>
                    </td>

                    {{-- Total Value --}}
                    <td class="px-4 py-3.5 text-right font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                        @if($deposit->total_value > 0)
                            Rs. {{ number_format($deposit->total_value) }}
                        @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>

                    {{-- Total Cash --}}
                    <td class="px-4 py-3.5 text-right font-semibold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                        @if($deposit->total_cash > 0)
                            Rs. {{ number_format($deposit->total_cash) }}
                        @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>

                    {{-- C+W --}}
                    <td class="px-4 py-3.5 text-right font-semibold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                        @if($deposit->total_cw > 0)
                            Rs. {{ number_format($deposit->total_cw) }}
                        @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>

                    {{-- Auto Status badge --}}
                    <td class="px-4 py-3.5 text-center">
                        @if($deposit->total_value > 0)
                        @php $as = $deposit->autoStatus(); @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                     {{ $as === 'Paid'
                                         ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                                         : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                            {{ $as }}
                        </span>
                        @else
                        <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Distribution Status badge --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1
                                     text-xs font-semibold {{ $deposit->statusBadgeClass() }}">
                            @if($deposit->isPending())
                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 dark:bg-yellow-400"></span>
                            @else
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                            @endif
                            {{ $deposit->statusLabel() }}
                        </span>
                    </td>

                    {{-- Action buttons --}}
                    <td class="px-4 py-3.5 text-right">
                        <div class="inline-flex items-center gap-1.5">

                            @if($deposit->isPending())

                                {{-- Distribute --}}
                                <a href="{{ route('bulk-deposits.distribute', $deposit) }}"
                                   title="Distribute to Daily Sales"
                                   class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 hover:bg-indigo-700
                                          px-2.5 py-1.5 text-xs font-semibold text-white transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    Distribute
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('bulk-deposits.edit', $deposit) }}"
                                   title="Edit this record"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 dark:border-slate-600
                                          bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700
                                          px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>

                                {{-- Delete (pending) --}}
                                <form method="POST"
                                      action="{{ route('bulk-deposits.destroy', $deposit) }}"
                                      onsubmit="return confirm('Delete bulk deposit #{{ $deposit->id }} for {{ addslashes($deposit->assistant->name ?? '') }}?\n\nThis action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Delete this record"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-800
                                                   bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20
                                                   px-2.5 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>

                            @else

                                {{-- Completed — Distribute locked --}}
                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 dark:bg-slate-700
                                             px-2.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Distributed
                                </span>

                                {{-- Delete (completed — destroys daily records too) --}}
                                <form method="POST"
                                      action="{{ route('bulk-deposits.destroy', $deposit) }}"
                                      onsubmit="return confirm('Delete COMPLETED bulk deposit #{{ $deposit->id }}?\n\nThis will also remove all {{ $deposit->dayCount() }} distributed daily sale record(s) for {{ addslashes($deposit->assistant->name ?? '') }} in this date range.\n\nThis CANNOT be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Delete record and its distributed daily data"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-800
                                                   bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20
                                                   px-2.5 py-1.5 text-xs font-medium text-red-500 dark:text-red-400 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>

                            @endif
                        </div>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($bulkDeposits->hasPages())
    <div class="border-t border-slate-100 dark:border-slate-700/60 px-5 py-3">
        {{ $bulkDeposits->links() }}
    </div>
    @endif
    @endif
</div>

</x-layouts.app>
