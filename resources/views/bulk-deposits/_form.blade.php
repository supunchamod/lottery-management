{{-- ══════════════════════════════════════════════════════════════════════
     SHARED FORM PARTIAL  —  included by create.blade.php & edit.blade.php
     Variables expected:
       $assistants  – Collection of SalesAssistant
       $deposit     – BulkDeposit model (may be empty/new for create)
       $formAction  – Route string (POST or PUT target)
       $formMethod  – 'POST' | 'PUT'
       $submitLabel – Button label text
═══════════════════════════════════════════════════════════════════════ --}}

@php
/* Seed values – old() takes priority (validation fail redirect), then model, then 0 */
$v = fn(string $field, $fallback = 0) =>
    old($field, isset($deposit) ? $deposit->$field : $fallback);
@endphp

<div x-data="bulkForm({
        qty:       {{ (int)   $v('total_qty') }},
        unitPrice: {{ (float) $v('unit_price', 40) }},
        d5:        {{ (int)   $v('denom_5') }},
        d10:       {{ (int)   $v('denom_10') }},
        d20:       {{ (int)   $v('denom_20') }},
        d50:       {{ (int)   $v('denom_50') }},
        d100:      {{ (int)   $v('denom_100') }},
        d500:      {{ (int)   $v('denom_500') }},
        d1000:     {{ (int)   $v('denom_1000') }},
        d5000:     {{ (int)   $v('denom_5000') }},
        nlb:       {{ (float) $v('nlb_winning') }},
        dlb:       {{ (float) $v('dlb_winning') }},
        tw:        {{ (float) $v('tw_winning') }}
     })"
     @keydown.escape.window="cashModal.open = false">

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if($formMethod === 'PUT') @method('PUT') @endif

    {{-- ── Section 1: Identity ─────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700/60">

        {{-- Assistant --}}
        <div class="px-5 py-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Sales Assistant <span class="text-red-500">*</span>
            </label>
            <select name="assistant_id"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                           px-3.5 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400
                           @error('assistant_id') border-red-400 dark:border-red-500 @enderror">
                <option value="">— Select assistant —</option>
                @foreach($assistants as $a)
                <option value="{{ $a->id }}"
                        {{ (string) $v('assistant_id') === (string) $a->id ? 'selected' : '' }}>
                    {{ $a->name }}
                </option>
                @endforeach
            </select>
            @error('assistant_id')
            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ $message }}
            </p>
            @enderror
        </div>

        {{-- Date range --}}
        <div class="grid grid-cols-2 gap-4 px-5 py-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    From Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date_from" value="{{ $v('date_from', today()->toDateString()) }}"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                              bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                              px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                              @error('date_from') border-red-400 @enderror">
                @error('date_from')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    To Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date_to" value="{{ $v('date_to', today()->toDateString()) }}"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                              bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                              px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                              @error('date_to') border-red-400 @enderror">
                @error('date_to')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- ── Section 2: Totals & Auto-Calculation ────────────────────────────── --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/30">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Summary Totals &amp; Auto-Calculation
            </p>
        </div>

        <div class="px-5 py-4 space-y-4">
            {{-- Qty + Unit Price + auto Value --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        Total Units
                    </label>
                    <input type="number" name="total_qty" min="0" step="1" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm text-center
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           :value="qty"
                           @input="qty = parseInt($event.target.value)||0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        Unit Price (Rs.)
                    </label>
                    <input type="number" name="unit_price" min="0" step="0.01" placeholder="40"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm text-center
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           :value="unitPrice"
                           @input="unitPrice = parseFloat($event.target.value)||0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        Total Value
                    </label>
                    <div class="rounded-xl border border-indigo-200 dark:border-indigo-800/50
                                bg-indigo-50/60 dark:bg-indigo-900/10
                                px-3 py-2 text-sm text-center font-bold
                                text-indigo-700 dark:text-indigo-300">
                        Rs. <span x-text="fmt(totalValue())"></span>
                    </div>
                </div>
            </div>

            {{-- Cash Counter button --}}
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        Cash Collected
                    </label>
                    <button type="button"
                            @click="openCashCounter()"
                            class="flex w-full items-center justify-between rounded-xl
                                   border px-3.5 py-2.5 text-sm font-medium transition-colors"
                            :class="autoCash() > 0
                                ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300'
                                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Click to Count Cash
                        </span>
                        <span class="font-bold" x-text="'Rs. ' + fmt(autoCash())"></span>
                    </button>

                    {{-- Hidden denomination fields --}}
                    <input type="hidden" name="denom_5"    :value="d5">
                    <input type="hidden" name="denom_10"   :value="d10">
                    <input type="hidden" name="denom_20"   :value="d20">
                    <input type="hidden" name="denom_50"   :value="d50">
                    <input type="hidden" name="denom_100"  :value="d100">
                    <input type="hidden" name="denom_500"  :value="d500">
                    <input type="hidden" name="denom_1000" :value="d1000">
                    <input type="hidden" name="denom_5000" :value="d5000">
                </div>
            </div>

            {{-- Winnings row --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        NLB Winnings
                    </label>
                    <input type="number" name="nlb_winning" min="0" step="0.01" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm text-center
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           :value="nlb"
                           @input="nlb = parseFloat($event.target.value)||0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        DLB Winnings
                    </label>
                    <input type="number" name="dlb_winning" min="0" step="0.01" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm text-center
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           :value="dlb"
                           @input="dlb = parseFloat($event.target.value)||0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                        Other Winnings (TW)
                    </label>
                    <input type="number" name="tw_winning" min="0" step="0.01" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm text-center
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           :value="tw"
                           @input="tw = parseFloat($event.target.value)||0">
                </div>
            </div>
        </div>

        {{-- Auto-Calc summary strip ──────────────────────────────────────────── --}}
        <div class="border-t border-slate-100 dark:border-slate-700/60
                    bg-slate-50/70 dark:bg-slate-900/30 px-5 py-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                {{-- Auto Cash --}}
                <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-3 py-2.5 text-center">
                    <p class="text-[10px] uppercase tracking-wide font-semibold text-slate-400 dark:text-slate-500 mb-1">
                        Auto Cash
                    </p>
                    <p class="text-base font-bold text-emerald-700 dark:text-emerald-400"
                       x-text="'Rs. ' + fmt(autoCash())"></p>
                </div>

                {{-- Auto C+W --}}
                <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-3 py-2.5 text-center">
                    <p class="text-[10px] uppercase tracking-wide font-semibold text-slate-400 dark:text-slate-500 mb-1">
                        Auto C+W
                    </p>
                    <p class="text-base font-bold text-indigo-600 dark:text-indigo-400"
                       x-text="'Rs. ' + fmt(autoCW())"></p>
                </div>

                {{-- Outstanding --}}
                <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-3 py-2.5 text-center">
                    <p class="text-[10px] uppercase tracking-wide font-semibold text-slate-400 dark:text-slate-500 mb-1">
                        Outstanding
                    </p>
                    <p class="text-base font-bold"
                       :class="autoOutstanding() > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400'"
                       x-text="'Rs. ' + fmt(autoOutstanding())"></p>
                </div>

                {{-- Auto Status --}}
                <div class="rounded-xl bg-white dark:bg-slate-800 ring-1 ring-black/5 dark:ring-white/5 px-3 py-2.5 text-center">
                    <p class="text-[10px] uppercase tracking-wide font-semibold text-slate-400 dark:text-slate-500 mb-1">
                        Auto Status
                    </p>
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                          :class="autoStatus() === 'Paid'
                              ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                              : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'"
                          x-text="autoStatus()"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Section 3: Notes ─────────────────────────────────────────────────── --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
            Notes <span class="text-slate-400 text-xs font-normal">(optional)</span>
        </label>
        <textarea name="notes" rows="2" placeholder="Any remarks about this bulk entry…"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                         bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                         px-3.5 py-2.5 text-sm resize-none
                         focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ $v('notes', '') }}</textarea>
    </div>

    {{-- ── Actions ──────────────────────────────────────────────────────────── --}}
    <div class="flex gap-3 pt-1">
        <a href="{{ route('bulk-deposits.index') }}"
           class="flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                  bg-white dark:bg-slate-800 py-2.5 text-center text-sm font-medium
                  text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
        </a>
        <button type="submit"
                class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-700
                       py-2.5 text-sm font-semibold text-white transition-colors">
            {{ $submitLabel }}
        </button>
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
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
     @click.self="cashModal.open = false"
     style="display:none;">

    <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-800 shadow-2xl overflow-hidden ring-1 ring-black/5 dark:ring-white/5"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between bg-slate-900 dark:bg-slate-950 px-5 py-4">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Cash Counter</p>
                <p class="font-bold text-white text-sm mt-0.5">Click to enter denomination counts</p>
            </div>
            <button type="button" @click="cashModal.open = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Denomination rows --}}
        <div class="px-5 py-4 space-y-2.5">
            @foreach([
                5000 => ['bg-purple-100 dark:bg-purple-900/30', 'text-purple-800 dark:text-purple-300'],
                1000 => ['bg-blue-100 dark:bg-blue-900/30',     'text-blue-800 dark:text-blue-300'],
                 500 => ['bg-emerald-100 dark:bg-emerald-900/30','text-emerald-800 dark:text-emerald-300'],
                 100 => ['bg-yellow-100 dark:bg-yellow-900/30', 'text-yellow-800 dark:text-yellow-300'],
                  50 => ['bg-orange-100 dark:bg-orange-900/30', 'text-orange-800 dark:text-orange-300'],
                  20 => ['bg-slate-100 dark:bg-slate-700',      'text-slate-700 dark:text-slate-300'],
                  10 => ['bg-red-100 dark:bg-red-900/30',       'text-red-800 dark:text-red-300'],
                   5 => ['bg-pink-100 dark:bg-pink-900/30',     'text-pink-800 dark:text-pink-300'],
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
                              focus:outline-none focus:ring-2 focus:ring-indigo-400"
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
                               text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition-colors">
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
<script>
function bulkForm(init) {
    return {
        qty:       init.qty       || 0,
        unitPrice: init.unitPrice || 0,
        d5:    init.d5    || 0,
        d10:   init.d10   || 0,
        d20:   init.d20   || 0,
        d50:   init.d50   || 0,
        d100:  init.d100  || 0,
        d500:  init.d500  || 0,
        d1000: init.d1000 || 0,
        d5000: init.d5000 || 0,
        nlb:   init.nlb   || 0,
        dlb:   init.dlb   || 0,
        tw:    init.tw    || 0,

        cashModal: {
            open: false,
            denoms: { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
        },

        // ── Reactive computed ─────────────────────────────────────────────────
        totalValue() {
            return this.qty * this.unitPrice;
        },
        autoCash() {
            return (this.d5*5)+(this.d10*10)+(this.d20*20)+(this.d50*50)
                  +(this.d100*100)+(this.d500*500)+(this.d1000*1000)+(this.d5000*5000);
        },
        totalWinning() {
            return this.nlb + this.dlb + this.tw;
        },
        autoCW() {
            return this.autoCash() + this.totalWinning();
        },
        autoOutstanding() {
            return Math.max(0, this.totalValue() - this.autoCW());
        },
        autoStatus() {
            return this.autoCW() >= this.totalValue() ? 'Paid' : 'Balance';
        },

        // ── Cash counter modal ────────────────────────────────────────────────
        openCashCounter() {
            this.cashModal.denoms = {
                5:this.d5, 10:this.d10, 20:this.d20, 50:this.d50,
                100:this.d100, 500:this.d500, 1000:this.d1000, 5000:this.d5000,
            };
            this.cashModal.open = true;
        },
        cashModalTotal() {
            const d = this.cashModal.denoms;
            return (d[5]||0)*5+(d[10]||0)*10+(d[20]||0)*20+(d[50]||0)*50
                  +(d[100]||0)*100+(d[500]||0)*500+(d[1000]||0)*1000+(d[5000]||0)*5000;
        },
        applyCash() {
            const d = this.cashModal.denoms;
            this.d5=d[5]||0;   this.d10=d[10]||0; this.d20=d[20]||0;  this.d50=d[50]||0;
            this.d100=d[100]||0; this.d500=d[500]||0; this.d1000=d[1000]||0; this.d5000=d[5000]||0;
            this.cashModal.open = false;
        },
        resetCash() {
            this.cashModal.denoms = { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 };
        },

        // ── Formatter ─────────────────────────────────────────────────────────
        fmt(n) {
            return Number(n||0).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
        },
    };
}
</script>
@endpush
