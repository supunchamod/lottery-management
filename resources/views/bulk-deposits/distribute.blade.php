<x-layouts.app title="Distribute Bulk Deposit">

{{-- ══════════════════════════════════════════════════════════════════════
     ALPINE ROOT
═══════════════════════════════════════════════════════════════════════ --}}
<div x-data="bulkDistribute(
        {{ json_encode($alpineRows) }},
        {{ json_encode($dates->all()) }}
     )"
     @keydown.escape.window="cashModal.open = false">

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
            <a href="{{ route('bulk-deposits.index') }}"
               class="hover:text-indigo-500 transition-colors">Bulk Deposits</a>
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-slate-700 dark:text-slate-300">Distribute</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Distribute: {{ $bulkDeposit->assistant->name }}
        </h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
            {{ $bulkDeposit->date_from->format('d M Y') }}
            →
            {{ $bulkDeposit->date_to->format('d M Y') }}
            &nbsp;·&nbsp;
            <span class="font-medium text-slate-600 dark:text-slate-300">
                {{ $bulkDeposit->dayCount() }} {{ Str::plural('day', $bulkDeposit->dayCount()) }}
            </span>
            @if($bulkDeposit->notes)
            &nbsp;·&nbsp;
            <span class="italic">{{ $bulkDeposit->notes }}</span>
            @endif
        </p>
    </div>

    {{-- Totals pill --}}
    <div class="flex flex-wrap gap-3 self-start mt-2 sm:mt-0">
        <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-4 py-2 text-center min-w-[100px]">
            <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide font-medium">Total Value</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-0.5"
               x-text="'Rs. ' + fmt(totalValue())"></p>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-4 py-2 text-center min-w-[100px]">
            <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide font-medium">Total Cash</p>
            <p class="text-base font-bold text-emerald-700 dark:text-emerald-400 mt-0.5"
               x-text="'Rs. ' + fmt(totalCash())"></p>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-4 py-2 text-center min-w-[100px]">
            <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide font-medium">Balance</p>
            <p class="text-base font-bold mt-0.5"
               :class="totalBalance() > 0 ? 'text-red-600 dark:text-red-400' : totalBalance() < 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500'"
               x-text="'Rs. ' + fmt(totalBalance())"></p>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MAIN FORM
═══════════════════════════════════════════════════════════════════════ --}}
<form id="dist-form" method="POST" action="{{ route('bulk-deposits.save-distribution', $bulkDeposit) }}"
      @submit="isDirty = false"
      @input="isDirty = true">
    @csrf

<div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/5 overflow-hidden">

    {{-- Table header --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="min-width:900px">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/40">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-32">Date</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-20">Qty</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-24">Unit Price</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-28">Value</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cash Counter</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-28">Cash</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-24">NLB Win</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-24">DLB Win</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-28">C+W</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 w-28">Balance</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">

                @foreach($dates as $date)
                @php $carbon = \Carbon\Carbon::parse($date); @endphp
                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">

                    {{-- Date label --}}
                    <td class="px-4 py-2.5">
                        <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs">
                            {{ $carbon->format('d M Y') }}
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wide">
                            {{ $carbon->format('l') }}
                        </div>
                    </td>

                    {{-- Qty --}}
                    <td class="px-3 py-2.5">
                        <input type="number" name="rows[{{ $date }}][qty]" min="0" step="1" placeholder="0"
                               class="ds-inp w-full rounded-lg border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-2 py-1.5 text-sm text-center
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                               :value="rows['{{ $date }}'].qty"
                               @input="rows['{{ $date }}'].qty = parseInt($event.target.value)||0">
                    </td>

                    {{-- Unit Price --}}
                    <td class="px-3 py-2.5">
                        <input type="number" name="rows[{{ $date }}][unit_price]" min="0" step="0.01" placeholder="40"
                               class="ds-inp w-full rounded-lg border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-2 py-1.5 text-sm text-center
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                               :value="rows['{{ $date }}'].unitPrice"
                               @input="rows['{{ $date }}'].unitPrice = parseFloat($event.target.value)||0">
                    </td>

                    {{-- Computed value --}}
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-semibold text-slate-700 dark:text-slate-300 text-xs"
                              x-text="'Rs. ' + fmt(value('{{ $date }}'))"></span>
                    </td>

                    {{-- Cash counter button --}}
                    <td class="px-3 py-2.5 text-center">
                        {{-- Hidden denomination fields (written by applyCash()) --}}
                        <input type="hidden" name="rows[{{ $date }}][d20]"   :value="rows['{{ $date }}'].d20">
                        <input type="hidden" name="rows[{{ $date }}][d50]"   :value="rows['{{ $date }}'].d50">
                        <input type="hidden" name="rows[{{ $date }}][d100]"  :value="rows['{{ $date }}'].d100">
                        <input type="hidden" name="rows[{{ $date }}][d500]"  :value="rows['{{ $date }}'].d500">
                        <input type="hidden" name="rows[{{ $date }}][d1000]" :value="rows['{{ $date }}'].d1000">
                        <input type="hidden" name="rows[{{ $date }}][d5000]" :value="rows['{{ $date }}'].d5000">

                        <button type="button"
                                @click="openCashCounter('{{ $date }}')"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors
                                       border border-slate-200 dark:border-slate-600
                                       bg-white dark:bg-slate-700/50
                                       text-slate-600 dark:text-slate-300
                                       hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-600
                                       hover:text-indigo-600 dark:hover:text-indigo-400"
                                :class="hasCash('{{ $date }}') ? 'border-emerald-300 dark:border-emerald-700 text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/10' : ''">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-text="hasCash('{{ $date }}') ? 'Rs. ' + fmt(cash('{{ $date }}')) : 'Add Cash'"></span>
                        </button>
                    </td>

                    {{-- Cash total --}}
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-semibold text-emerald-700 dark:text-emerald-400 text-xs"
                              x-text="'Rs. ' + fmt(cash('{{ $date }}'))"></span>
                    </td>

                    {{-- NLB Winning --}}
                    <td class="px-3 py-2.5">
                        <input type="number" name="rows[{{ $date }}][nlb_winning]" min="0" step="0.01" placeholder="0"
                               class="ds-inp w-full rounded-lg border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-2 py-1.5 text-sm text-center
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                               :value="rows['{{ $date }}'].nlbWinning"
                               @input="rows['{{ $date }}'].nlbWinning = parseFloat($event.target.value)||0">
                    </td>

                    {{-- DLB Winning --}}
                    <td class="px-3 py-2.5">
                        <input type="number" name="rows[{{ $date }}][dlb_winning]" min="0" step="0.01" placeholder="0"
                               class="ds-inp w-full rounded-lg border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-2 py-1.5 text-sm text-center
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                               :value="rows['{{ $date }}'].dlbWinning"
                               @input="rows['{{ $date }}'].dlbWinning = parseFloat($event.target.value)||0">
                    </td>

                    {{-- C+W --}}
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400 text-xs"
                              x-text="'Rs. ' + fmt(cw('{{ $date }}'))"></span>
                    </td>

                    {{-- Balance --}}
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-bold text-xs"
                              :class="balance('{{ $date }}') > 0 ? 'text-red-600 dark:text-red-400' :
                                      balance('{{ $date }}') < 0 ? 'text-indigo-600 dark:text-indigo-400' :
                                      'text-slate-400 dark:text-slate-500'"
                              x-text="'Rs. ' + fmt(balance('{{ $date }}'))"></span>
                    </td>

                    {{-- Remarks --}}
                    <td class="px-3 py-2.5">
                        <input type="text" name="rows[{{ $date }}][remarks]" placeholder="—"
                               class="ds-inp w-full min-w-[120px] rounded-lg border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-2 py-1.5 text-xs
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                               :value="rows['{{ $date }}'].remarks"
                               @input="rows['{{ $date }}'].remarks = $event.target.value">
                    </td>

                </tr>
                @endforeach

            </tbody>

            {{-- Totals footer --}}
            <tfoot>
                <tr class="border-t-2 border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40">
                    <td colspan="3" class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Totals
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm text-slate-800 dark:text-slate-200"
                              x-text="'Rs. ' + fmt(totalValue())"></span>
                    </td>
                    <td class="px-3 py-3"></td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm text-emerald-700 dark:text-emerald-400"
                              x-text="'Rs. ' + fmt(totalCash())"></span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm text-slate-700 dark:text-slate-300"
                              x-text="'Rs. ' + fmt(totalNlb())"></span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm text-slate-700 dark:text-slate-300"
                              x-text="'Rs. ' + fmt(totalDlb())"></span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm text-indigo-600 dark:text-indigo-400"
                              x-text="'Rs. ' + fmt(totalCW())"></span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-bold text-sm"
                              :class="totalBalance() > 0 ? 'text-red-600 dark:text-red-400' :
                                      totalBalance() < 0 ? 'text-indigo-600 dark:text-indigo-400' :
                                      'text-slate-400 dark:text-slate-500'"
                              x-text="'Rs. ' + fmt(totalBalance())"></span>
                    </td>
                    <td class="px-3 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Action bar --}}
    <div class="border-t border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/40 px-5 py-4 flex items-center justify-between gap-4">
        <p class="text-xs text-slate-400 dark:text-slate-500">
            Saving will create/update {{ $bulkDeposit->dayCount() }} daily sales record(s) and mark this bulk entry as
            <strong class="text-emerald-600 dark:text-emerald-400">Completed</strong>.
            Empty rows are skipped automatically.
        </p>
        <div class="flex gap-3 shrink-0">
            <a href="{{ route('bulk-deposits.index') }}"
               class="rounded-xl border border-slate-300 dark:border-slate-600
                      bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-medium
                      text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-xl bg-emerald-600 hover:bg-emerald-700
                           px-6 py-2.5 text-sm font-semibold text-white transition-colors">
                Save &amp; Distribute
            </button>
        </div>
    </div>

</div>
</form>

{{-- ══════════════════════════════════════════════════════════════════════
     CASH COUNTER MODAL
═══════════════════════════════════════════════════════════════════════ --}}
<div x-show="cashModal.open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden"
     @click.self="cashModal.open = false"
     style="display:none;">

    <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-800 shadow-2xl overflow-hidden ring-1 ring-black/5 dark:ring-white/5"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Modal header --}}
        <div class="flex items-center justify-between bg-slate-900 dark:bg-slate-950 px-5 py-4">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Cash Counter</p>
                <p class="font-bold text-white text-sm mt-0.5"
                   x-text="cashModal.date ? cashModal.date : ''"></p>
            </div>
            <button type="button" @click="cashModal.open = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Denomination inputs --}}
        <div class="px-5 py-4 space-y-2.5">
            @foreach([
                5000 => ['bg-purple-100 dark:bg-purple-900/30','text-purple-800 dark:text-purple-300'],
                1000 => ['bg-blue-100 dark:bg-blue-900/30','text-blue-800 dark:text-blue-300'],
                 500 => ['bg-emerald-100 dark:bg-emerald-900/30','text-emerald-800 dark:text-emerald-300'],
                 100 => ['bg-yellow-100 dark:bg-yellow-900/30','text-yellow-800 dark:text-yellow-300'],
                  50 => ['bg-orange-100 dark:bg-orange-900/30','text-orange-800 dark:text-orange-300'],
                  20 => ['bg-slate-100 dark:bg-slate-700','text-slate-700 dark:text-slate-300'],
            ] as $denom => $cls)
            <div class="flex items-center gap-3">
                <span class="w-20 flex-shrink-0 rounded-full {{ $cls[0] }} {{ $cls[1] }} px-3 py-1 text-center text-xs font-bold">
                    Rs. {{ number_format($denom) }}
                </span>
                <span class="text-slate-400 dark:text-slate-500">×</span>
                <input type="number" min="0" step="1" placeholder="0"
                       class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700
                              bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                              px-3 py-1.5 text-sm text-center font-medium
                              focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                       :value="cashModal.denoms[{{ $denom }}]"
                       @input="cashModal.denoms[{{ $denom }}] = parseInt($event.target.value)||0"
                       @keydown.enter.prevent="applyCash()">
                <span class="w-24 flex-shrink-0 text-right text-sm font-semibold text-slate-700 dark:text-slate-300"
                      x-text="'Rs. ' + ((cashModal.denoms[{{ $denom }}]||0)*{{ $denom }}).toLocaleString()"></span>
            </div>
            @endforeach
        </div>

        {{-- Total + buttons --}}
        <div class="border-t border-slate-100 dark:border-slate-700/60
                    bg-slate-50 dark:bg-slate-900/40 px-5 py-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Cash</span>
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-400"
                      x-text="'Rs. ' + fmt(cashModalTotal())"></span>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="cashModal.open = false"
                        class="flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                               bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                               text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="resetCash()"
                        class="flex-1 rounded-xl border border-red-300 dark:border-red-600
                               bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                               text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Reset
                </button>
                <button type="button" @click="applyCash()"
                        class="flex-1 rounded-xl bg-emerald-600 hover:bg-emerald-700
                               py-2.5 text-sm font-semibold text-white transition-colors">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

</div>{{-- /x-data --}}

@push('head')
<style>
input.ds-inp::-webkit-outer-spin-button,
input.ds-inp::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.ds-inp[type=number] { -moz-appearance: textfield; }
input.ds-inp:focus {
    background: rgba(99,102,241,0.06) !important;
    box-shadow: inset 0 0 0 2px #6366f1;
}
.dark input.ds-inp:focus {
    background: rgba(99,102,241,0.15) !important;
    box-shadow: inset 0 0 0 2px #818cf8;
}
</style>
<script>
function bulkDistribute(initialRows, dates) {
    return {
        rows: initialRows,
        dates: dates,
        isDirty: false,
        pendingUrl: null,
        _formId: 'dist-form',
        cashModal: {
            open: false,
            date: null,
            denoms: { 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
        },

        // ── DLP lifecycle ─────────────────────────────────────────────────────
        init() {
            // 1. Browser-level: warn on tab close / refresh / back-button
            this._unloadHandler = (e) => {
                if (!this.isDirty) return;
                e.preventDefault();
                e.returnValue = '';
            };
            window.addEventListener('beforeunload', this._unloadHandler);

            // 2. App-level: intercept all nav-link clicks when dirty
            this._clickGuard = (e) => {
                if (!this.isDirty) return;
                const a = e.target.closest('a[href]');
                if (!a) return;
                const href = a.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:')) return;
                e.preventDefault();
                this._dlpPrompt(a.href);
            };
            document.addEventListener('click', this._clickGuard);
        },
        destroy() {
            window.removeEventListener('beforeunload', this._unloadHandler);
            document.removeEventListener('click', this._clickGuard);
        },

        // ── SweetAlert2 DLP prompt ────────────────────────────────────────────
        _dlpPrompt(destUrl) {
            this.pendingUrl = destUrl;
            const dark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Unsaved Changes',
                html: 'Your distribution table has unsaved entries.<br><small style="color:#94a3b8">Choose how to proceed:</small>',
                icon: 'warning',
                iconColor: '#f59e0b',
                background: dark ? '#1e293b' : '#ffffff',
                color: dark ? '#e2e8f0' : '#1e293b',
                showConfirmButton: true,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Save &amp; Go',
                denyButtonText: 'Discard &amp; Leave',
                cancelButtonText: 'Keep Editing',
                confirmButtonColor: '#4f46e5',
                denyButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                allowEscapeKey: true,
                preConfirm: async () => {
                    const form = document.getElementById(this._formId);
                    const res = await fetch(form.action, { method: 'POST', body: new FormData(form) })
                        .catch(() => null);
                    if (!res || !res.ok) {
                        Swal.showValidationMessage('Save failed — please try again.');
                        return false;
                    }
                    return true;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    this.isDirty = false;
                    window.location.href = this.pendingUrl;
                } else if (result.isDenied) {
                    this.isDirty = false;
                    window.location.href = this.pendingUrl;
                }
                // isDismissed = "Keep Editing" → do nothing
            });
        },

        // ── Per-row computed ──────────────────────────────────────────────────
        value(date) {
            return (this.rows[date].qty || 0) * (this.rows[date].unitPrice || 0);
        },
        cash(date) {
            const r = this.rows[date];
            return (r.d20||0)*20 + (r.d50||0)*50 + (r.d100||0)*100
                 + (r.d500||0)*500 + (r.d1000||0)*1000 + (r.d5000||0)*5000;
        },
        hasCash(date) {
            const r = this.rows[date];
            return (r.d20||0)+(r.d50||0)+(r.d100||0)+(r.d500||0)+(r.d1000||0)+(r.d5000||0) > 0;
        },
        cw(date) {
            return this.cash(date)
                 + (this.rows[date].nlbWinning || 0)
                 + (this.rows[date].dlbWinning || 0);
        },
        balance(date) {
            return this.value(date) - this.cw(date);
        },

        // ── Grand totals ──────────────────────────────────────────────────────
        totalValue()   { return this.dates.reduce((s, d) => s + this.value(d),   0); },
        totalCash()    { return this.dates.reduce((s, d) => s + this.cash(d),    0); },
        totalNlb()     { return this.dates.reduce((s, d) => s + (this.rows[d].nlbWinning||0), 0); },
        totalDlb()     { return this.dates.reduce((s, d) => s + (this.rows[d].dlbWinning||0), 0); },
        totalCW()      { return this.dates.reduce((s, d) => s + this.cw(d),      0); },
        totalBalance() { return this.dates.reduce((s, d) => s + this.balance(d), 0); },

        // ── Cash counter modal ────────────────────────────────────────────────
        openCashCounter(date) {
            const r = this.rows[date];
            this.cashModal.denoms = {
                20: r.d20||0, 50: r.d50||0, 100: r.d100||0,
                500: r.d500||0, 1000: r.d1000||0, 5000: r.d5000||0,
            };
            this.cashModal.date = date;
            this.cashModal.open = true;
        },
        cashModalTotal() {
            const d = this.cashModal.denoms;
            return (d[20]||0)*20 + (d[50]||0)*50 + (d[100]||0)*100
                 + (d[500]||0)*500 + (d[1000]||0)*1000 + (d[5000]||0)*5000;
        },
        applyCash() {
            const date = this.cashModal.date;
            const d    = this.cashModal.denoms;
            Object.assign(this.rows[date], {
                d20:d[20]||0, d50:d[50]||0, d100:d[100]||0,
                d500:d[500]||0, d1000:d[1000]||0, d5000:d[5000]||0,
            });
            this.isDirty = true;
            this.cashModal.open = false;
        },
        resetCash() {
            this.cashModal.denoms = { 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 };
        },

        // ── Formatter ─────────────────────────────────────────────────────────
        fmt(n) {
            return Number(n||0).toLocaleString('en-US', {
                minimumFractionDigits: 0, maximumFractionDigits: 0,
            });
        },
    };
}
</script>
@endpush

</x-layouts.app>
