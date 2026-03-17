<x-layouts.app title="Scam Ticket Management">

{{-- ── Top bar ─────────────────────────────────────────────────────────────── --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        Track inflated / fraudulent winning tickets reported by sales assistants.
    </p>
    <a href="{{ route('scam-winnings.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700
              px-4 py-2 text-sm font-semibold text-white shadow-md transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Scam Ticket
    </a>
</div>

{{-- ── Barcode Alert Scanner ───────────────────────────────────────────────── --}}
<div class="mb-4 rounded-2xl border border-amber-200 dark:border-amber-700/50
            bg-amber-50 dark:bg-amber-900/10 p-4 print:hidden"
     x-data="barcodeChecker()">
    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide mb-2">
        🔍 Barcode Cross-Check — Scan a return ticket to check for scam history
    </p>
    <div class="flex items-center gap-3">
        <input type="text"
               x-model="barcode"
               @keydown.enter.prevent="check()"
               placeholder="Scan or type barcode / serial number…"
               class="flex-1 rounded-xl border border-amber-200 dark:border-amber-700
                      bg-white dark:bg-slate-800 px-3 py-2 text-sm
                      text-slate-800 dark:text-white
                      focus:outline-none focus:ring-2 focus:ring-amber-400 dark:focus:ring-amber-500/50
                      placeholder-slate-400">
        <button type="button" @click="check()"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 hover:bg-amber-700
                       px-4 py-2 text-sm font-semibold text-white transition-colors">
            Check
        </button>
        <button type="button" @click="reset()" x-show="result !== null" x-cloak
                class="rounded-xl border border-slate-200 dark:border-slate-700
                       bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-500
                       hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Clear
        </button>
    </div>

    {{-- Loading --}}
    <p x-show="loading" x-cloak class="mt-2 text-xs text-amber-600 dark:text-amber-400 animate-pulse">
        Checking…
    </p>

    {{-- Alert: scam history found --}}
    <div x-show="result && result.has_alert" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-3 rounded-xl bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700/50 p-3">
        <p class="text-sm font-bold text-red-700 dark:text-red-300 mb-1">
            ⚠️ Scam History Detected!
        </p>
        <template x-if="result && result.barcode_scams && result.barcode_scams.length > 0">
            <div>
                <p class="text-xs text-red-600 dark:text-red-400 font-semibold mb-1">This exact barcode has scam records:</p>
                <template x-for="s in result.barcode_scams" :key="s.id">
                    <div class="text-xs text-red-700 dark:text-red-300 mb-0.5"
                         x-text="'• ' + (s.assistant?.name ?? 'Unknown') + ' — Rs. ' + s.difference + ' (' + s.date + ')'">
                    </div>
                </template>
            </div>
        </template>
        <template x-if="result && result.assistant_history && result.assistant_history.length > 0">
            <div class="mt-2">
                <p class="text-xs text-red-600 dark:text-red-400 font-semibold mb-1">Assistant scam history:</p>
                <template x-for="s in result.assistant_history" :key="s.id">
                    <div class="text-xs text-red-700 dark:text-red-300 mb-0.5"
                         x-text="'• ' + s.ticket_barcode + ' — Rs. ' + s.difference + ' (' + s.date + ') ' + (s.is_paid_back ? '✓ Paid' : '⚠ Unpaid')">
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- No history --}}
    <div x-show="result && !result.has_alert" x-cloak
         class="mt-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-300 dark:border-emerald-700/50 p-3">
        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">
            ✓ No scam history found for this barcode.
        </p>
    </div>
</div>

{{-- ── Filter Bar ──────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('scam-winnings.index') }}"
      class="mb-4 flex flex-wrap items-end gap-3 print:hidden">
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Assistant</label>
        <select name="assistant_id" class="erp-input text-sm h-9 w-full">
            <option value="">All Assistants</option>
            @foreach($assistants as $a)
                <option value="{{ $a->id }}" {{ request('assistant_id') == $a->id ? 'selected' : '' }}>
                    {{ $a->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Paid Back</label>
        <select name="paid" class="erp-input text-sm h-9">
            <option value="">All</option>
            <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Unpaid</option>
            <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Paid</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Barcode</label>
        <input type="text" name="barcode" value="{{ request('barcode') }}"
               placeholder="Barcode…" class="erp-input text-sm h-9">
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
    @if(request()->anyFilled(['assistant_id','paid','barcode','from','to']))
        <a href="{{ route('scam-winnings.index') }}"
           class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium
                  text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700
                  transition-colors h-9">
            Clear
        </a>
    @endif
</form>

{{-- ── Summary Tiles ───────────────────────────────────────────────────────── --}}
<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-5">
    <div class="rounded-2xl bg-slate-800 dark:bg-slate-700/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-slate-400 mb-1 font-medium">Reported Total</p>
        <p class="text-base font-bold">Rs. {{ number_format($totals['total_reported'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-indigo-600 dark:bg-indigo-600/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-indigo-200 mb-1 font-medium">Actual Total</p>
        <p class="text-base font-bold">Rs. {{ number_format($totals['total_actual'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-red-600 dark:bg-red-600/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-red-200 mb-1 font-medium">Total Scam Diff</p>
        <p class="text-base font-bold">Rs. {{ number_format($totals['total_diff'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-emerald-600 dark:bg-emerald-600/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-emerald-200 mb-1 font-medium">Paid Back</p>
        <p class="text-base font-bold">Rs. {{ number_format($totals['total_paid'], 2) }}</p>
    </div>
    <div class="rounded-2xl bg-amber-500 dark:bg-amber-500/80 px-4 py-3.5 text-white shadow-sm">
        <p class="text-xs text-amber-100 mb-1 font-medium">Still Owed</p>
        <p class="text-base font-bold">Rs. {{ number_format($totals['total_owed'], 2) }}</p>
    </div>
</div>

{{-- ── Table ───────────────────────────────────────────────────────────────── --}}
<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700/60
            bg-white dark:bg-slate-800/60 shadow-sm">
    <table class="min-w-full border-collapse text-xs">
        <thead>
            <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Assistant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Barcode</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Reported</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Actual</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Difference</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Paid Back</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Remaining</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide print:hidden">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
            @forelse($scams as $scam)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                x-data="{ payOpen: false }">
                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
                    {{ $scam->date->format('d M Y') }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-700 dark:text-slate-300">
                    {{ $scam->assistant->name ?? '—' }}
                </td>
                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">
                    {{ $scam->ticket_barcode }}
                </td>
                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    Rs. {{ number_format($scam->reported_winning_value, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                    Rs. {{ number_format($scam->actual_winning_value, 2) }}
                </td>
                <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400 whitespace-nowrap">
                    Rs. {{ number_format($scam->difference, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                    Rs. {{ number_format($scam->paid_back_amount, 2) }}
                </td>
                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap
                           {{ $scam->remainingAmount() > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400' }}">
                    Rs. {{ number_format($scam->remainingAmount(), 2) }}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    @if($scam->is_paid_back)
                        <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-900/30
                                     px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            Paid Back
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-red-100 dark:bg-red-900/30
                                     px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                            Unpaid
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap print:hidden">
                    <div class="flex items-center justify-center gap-1.5">
                        @if(!$scam->is_paid_back)
                        <button type="button" @click="payOpen = !payOpen"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700
                                       px-2.5 py-1.5 text-xs font-semibold text-white transition-colors">
                            Pay Back
                        </button>
                        @endif

                        <form method="POST" action="{{ route('scam-winnings.destroy', $scam) }}"
                              onsubmit="return confirm('Delete this scam record?')">
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

                    {{-- Inline pay-back form --}}
                    @if(!$scam->is_paid_back)
                    <div x-show="payOpen" x-cloak class="mt-2 flex items-center gap-2">
                        <form method="POST" action="{{ route('scam-winnings.mark-paid-back', $scam) }}"
                              class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="paid_back_amount" min="0.01" step="0.01"
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
                <td colspan="10" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500 text-sm">
                    No scam records found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4 print:hidden">
    {{ $scams->links() }}
</div>

@push('scripts')
<script>
function barcodeChecker() {
    return {
        barcode: '',
        loading: false,
        result:  null,

        async check() {
            if (!this.barcode.trim()) return;
            this.loading = true;
            this.result  = null;
            try {
                const url = '{{ route('api.scam-winnings.check-barcode') }}'
                    + '?barcode=' + encodeURIComponent(this.barcode.trim());
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                this.result = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        reset() {
            this.barcode = '';
            this.result  = null;
        },
    };
}
</script>
@endpush

</x-layouts.app>
