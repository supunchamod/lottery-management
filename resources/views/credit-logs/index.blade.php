<x-layouts.app title="Credit Logs">

{{-- ── Filter Bar ──────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('credit-logs.index') }}"
      class="mb-4 flex flex-wrap items-end gap-3 print:hidden">

    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Assistant</label>
        <select name="assistant_id"
                class="erp-input text-sm h-9 w-full">
            <option value="">All Assistants</option>
            @foreach($assistants as $a)
                <option value="{{ $a->id }}" {{ request('assistant_id') == $a->id ? 'selected' : '' }}>
                    {{ $a->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Status</label>
        <select name="status" class="erp-input text-sm h-9">
            <option value="">All</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Paid</option>
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="erp-input text-sm h-9">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="erp-input text-sm h-9">
    </div>

    <button type="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700
                   px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors h-9">
        Filter
    </button>
    @if(request()->anyFilled(['assistant_id','status','from','to']))
        <a href="{{ route('credit-logs.index') }}"
           class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium
                  text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700
                  transition-colors h-9">
            Clear
        </a>
    @endif
</form>

{{-- ── Summary Tiles ───────────────────────────────────────────────────────── --}}
<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <div class="rounded-2xl bg-slate-800 dark:bg-slate-700/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-slate-400 mb-1 font-medium">Total Credit</p>
        <p class="text-lg font-bold">Rs. {{ number_format($totals['total_amount'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-emerald-600 dark:bg-emerald-600/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-emerald-200 mb-1 font-medium">Total Paid Back</p>
        <p class="text-lg font-bold">Rs. {{ number_format($totals['total_paid'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-red-600 dark:bg-red-600/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-red-200 mb-1 font-medium">Still Pending</p>
        <p class="text-lg font-bold">Rs. {{ number_format($totals['total_pending'], 2) }}</p>
    </div>
</div>

{{-- ── Table ───────────────────────────────────────────────────────────────── --}}
<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700/60
            bg-white dark:bg-slate-800/60 shadow-sm">
    <table class="min-w-full border-collapse text-sm">
        <thead>
            <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Assistant</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Amount</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Paid</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Remaining</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Notes</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide print:hidden">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
            @forelse($logs as $log)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                x-data="{ payOpen: false, payAmount: 0 }">
                <td class="px-4 py-3 whitespace-nowrap text-slate-700 dark:text-slate-300 font-medium">
                    {{ $log->date->format('d M Y') }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-700 dark:text-slate-300">
                    {{ $log->assistant->name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    Rs. {{ number_format($log->amount, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                    Rs. {{ number_format($log->paid_amount, 2) }}
                </td>
                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap
                           {{ $log->remainingAmount() > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400' }}">
                    Rs. {{ number_format($log->remainingAmount(), 2) }}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    @if($log->status === 'paid')
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30
                                     px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            Paid
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30
                                     px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                            Pending
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 max-w-xs truncate text-xs">
                    {{ $log->notes ?: '—' }}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap print:hidden">
                    <div class="flex items-center justify-center gap-1.5">
                        @if($log->status === 'pending')
                        {{-- Mark Paid button --}}
                        <button type="button"
                                @click="payOpen = !payOpen"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700
                                       px-2.5 py-1.5 text-xs font-semibold text-white transition-colors">
                            Pay Back
                        </button>
                        @endif

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('credit-logs.destroy', $log) }}"
                              onsubmit="return confirm('Delete this credit log?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-700
                                           bg-white dark:bg-slate-800 px-2 py-1.5 text-xs text-red-600 dark:text-red-400
                                           hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    {{-- Inline pay form --}}
                    @if($log->status === 'pending')
                    <div x-show="payOpen" x-cloak
                         class="mt-2 flex items-center gap-2">
                        <form method="POST" action="{{ route('credit-logs.mark-paid', $log) }}"
                              class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="paid_amount" min="0.01" step="0.01"
                                   :max="{{ $log->remainingAmount() }}"
                                   placeholder="Amount"
                                   class="w-28 rounded-lg border border-slate-200 dark:border-slate-600
                                          bg-white dark:bg-slate-700 px-2 py-1 text-xs text-slate-800 dark:text-white
                                          focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            <button type="submit"
                                    class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white">
                                ✓
                            </button>
                            <button type="button" @click="payOpen = false"
                                    class="rounded-lg border border-slate-200 dark:border-slate-600 px-2 py-1 text-xs text-slate-500">✕</button>
                        </form>
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500 text-sm">
                    No credit logs found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4 print:hidden">
    {{ $logs->links() }}
</div>

</x-layouts.app>
