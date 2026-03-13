<x-layouts.app title="Bulk Deposits">

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bulk Deposits</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
            Buffer of multi-day sales entries pending distribution to Daily Sales.
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
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/40">
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Assistant</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date Range</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Days</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Created</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($bulkDeposits as $deposit)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 font-mono text-xs">#{{ $deposit->id }}</td>

                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-slate-900 dark:text-white">
                            {{ $deposit->assistant->name ?? '—' }}
                        </span>
                    </td>

                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">
                        {{ $deposit->date_from->format('d M Y') }}
                        <span class="text-slate-400 dark:text-slate-500 mx-1">→</span>
                        {{ $deposit->date_to->format('d M Y') }}
                    </td>

                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700
                                     px-2.5 py-0.5 text-xs font-bold text-slate-600 dark:text-slate-300">
                            {{ $deposit->dayCount() }}
                        </span>
                    </td>

                    <td class="px-5 py-3.5 text-center">
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

                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                        {{ $deposit->notes ?: '—' }}
                    </td>

                    <td class="px-5 py-3.5 text-xs text-slate-400 dark:text-slate-500">
                        {{ $deposit->created_at->format('d M Y, H:i') }}
                        @if($deposit->createdBy)
                        <span class="block text-slate-300 dark:text-slate-600">
                            by {{ $deposit->createdBy->name }}
                        </span>
                        @endif
                    </td>

                    <td class="px-5 py-3.5 text-right">
                        @if($deposit->isPending())
                        <a href="{{ route('bulk-deposits.distribute', $deposit) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700
                                  px-3 py-1.5 text-xs font-semibold text-white transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Distribute to Daily
                        </a>
                        @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 dark:bg-slate-700
                                     px-3 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Distributed
                        </span>
                        @endif
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
