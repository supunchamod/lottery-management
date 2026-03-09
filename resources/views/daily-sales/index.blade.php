<x-layouts.app title="Daily Sales Entry">

@php
    $parsedDate = \Carbon\Carbon::parse($date);
    $assistantNames = $assistants->pluck('name', 'id')->toArray();
@endphp

{{-- ══════════════════════════════════════════════════════════════════
     TOP BAR
═══════════════════════════════════════════════════════════════════ --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">

    <div class="flex items-center gap-2">
        <a href="{{ route('daily-sales.index', ['date' => $parsedDate->copy()->subDay()->toDateString()]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">‹</a>
        <form method="GET" action="{{ route('daily-sales.index') }}">
            <input type="date" name="date" value="{{ $date }}"
                   onchange="this.form.submit()"
                   class="erp-input text-sm h-9 font-semibold">
        </form>
        <a href="{{ route('daily-sales.index', ['date' => $parsedDate->copy()->addDay()->toDateString()]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50
                  {{ $parsedDate->isToday() ? 'opacity-40 pointer-events-none' : '' }}">›</a>
        <span class="text-sm font-semibold text-gray-700">{{ $parsedDate->format('l') }}</span>
        @if($parsedDate->isToday())
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Today</span>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('daily-sales.analysis') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Analysis
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700 print:hidden">
        {{ session('success') }}
    </div>
@endif

@if($assistants->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center text-sm text-gray-400">
        No assistants configured.
        <a href="{{ route('assistants.create') }}" class="text-blue-600 hover:underline">Add assistants →</a>
    </div>
@else

{{-- ══════════════════════════════════════════════════════════════════
     Alpine.js — Sales Grid + Cash Counter Modal
═══════════════════════════════════════════════════════════════════ --}}
<div x-data="salesGrid({{ json_encode($alpineRows) }}, {{ json_encode($assistantNames) }})"
     @keydown.escape.window="cashModal.open = false">

    {{-- ── Live Day Summary ──────────────────────────────────────────── --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-xl bg-gray-900 px-4 py-3 text-white">
            <p class="text-xs text-gray-400 mb-0.5">Total Value</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalValue())"></p>
        </div>
        <div class="rounded-xl bg-emerald-600 px-4 py-3 text-white">
            <p class="text-xs text-emerald-200 mb-0.5">Total Cash</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalCash())"></p>
        </div>
        <div class="rounded-xl bg-violet-600 px-4 py-3 text-white">
            <p class="text-xs text-violet-200 mb-0.5">Total Winning</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalWinning())"></p>
        </div>
        <div class="rounded-xl bg-blue-600 px-4 py-3 text-white">
            <p class="text-xs text-blue-200 mb-0.5">Total C+W</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalCW())"></p>
        </div>
        <div class="rounded-xl px-4 py-3 text-white"
             :class="totalBalance() > 0 ? 'bg-red-600' : (totalBalance() < 0 ? 'bg-amber-500' : 'bg-gray-500')">
            <p class="text-xs opacity-75 mb-0.5">Outstanding</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(Math.abs(totalBalance()))"></p>
        </div>
    </div>

    {{-- ── Form ─────────────────────────────────────────────────────── --}}
    <form id="sales-form" method="POST" action="{{ route('daily-sales.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        {{-- Hidden inputs synced from Alpine --}}
        @foreach($assistants as $a)
        @php $aid = $a->id; @endphp
        <input type="hidden" :name="`rows[{{ $aid }}][qty]`"         :value="rows[{{ $aid }}].qty">
        <input type="hidden" :name="`rows[{{ $aid }}][unit_price]`"  :value="rows[{{ $aid }}].unitPrice">
        <input type="hidden" :name="`rows[{{ $aid }}][d20]`"         :value="rows[{{ $aid }}].d20">
        <input type="hidden" :name="`rows[{{ $aid }}][d50]`"         :value="rows[{{ $aid }}].d50">
        <input type="hidden" :name="`rows[{{ $aid }}][d100]`"        :value="rows[{{ $aid }}].d100">
        <input type="hidden" :name="`rows[{{ $aid }}][d500]`"        :value="rows[{{ $aid }}].d500">
        <input type="hidden" :name="`rows[{{ $aid }}][d1000]`"       :value="rows[{{ $aid }}].d1000">
        <input type="hidden" :name="`rows[{{ $aid }}][d5000]`"       :value="rows[{{ $aid }}].d5000">
        <input type="hidden" :name="`rows[{{ $aid }}][nlb_winning]`" :value="rows[{{ $aid }}].nlbWinning">
        <input type="hidden" :name="`rows[{{ $aid }}][dlb_winning]`" :value="rows[{{ $aid }}].dlbWinning">
        <input type="hidden" :name="`rows[{{ $aid }}][remarks]`"     :value="rows[{{ $aid }}].remarks">
        @endforeach

        {{-- Save button --}}
        <div class="mb-3 flex items-center justify-between print:hidden">
            <span class="text-sm font-semibold text-gray-700">
                {{ $parsedDate->format('Y-m-d') }} — {{ $parsedDate->format('l') }}
            </span>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save All Records
            </button>
        </div>

        {{-- ── GRID TABLE ───────────────────────────────────────────── --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full border-collapse text-xs" id="sales-table">

                {{-- Column Headers --}}
                <thead>
                    <tr style="background:#0f172a;">
                        <th class="sticky left-0 z-20 px-3 py-3 text-left text-white font-medium whitespace-nowrap border-r border-slate-700"
                            style="background:#0f172a; min-width:150px;">#&nbsp; Name</th>
                        <th class="px-2 py-3 text-center text-blue-300 font-medium whitespace-nowrap" style="min-width:68px;">Amount</th>
                        <th class="px-2 py-3 text-center text-blue-200 font-medium whitespace-nowrap" style="min-width:60px;">Unit<br>Price</th>
                        <th class="px-2 py-3 text-center text-yellow-300 font-medium whitespace-nowrap" style="min-width:72px;">
                            Value<br><span class="text-gray-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-emerald-300 font-medium whitespace-nowrap" style="min-width:80px;">
                            Cash<br><span class="text-gray-500 font-normal" style="font-size:10px;">click to count</span>
                        </th>
                        <th class="px-2 py-3 text-center text-violet-300 font-medium whitespace-nowrap" style="min-width:66px;">NLB<br>Winning</th>
                        <th class="px-2 py-3 text-center text-violet-300 font-medium whitespace-nowrap" style="min-width:66px;">DLB<br>Winning</th>
                        <th class="px-2 py-3 text-center text-purple-300 font-medium whitespace-nowrap" style="min-width:62px;">
                            TW<br><span class="text-gray-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-cyan-300 font-medium whitespace-nowrap" style="min-width:62px;">
                            C+W<br><span class="text-gray-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-red-300 font-medium whitespace-nowrap" style="min-width:90px;">
                            Status<br><span class="text-gray-500 font-normal" style="font-size:10px;">Balance</span>
                        </th>
                        <th class="px-2 py-3 text-center text-gray-300 font-medium whitespace-nowrap" style="min-width:130px;">Remarks</th>
                    </tr>

                    {{-- Top totals row --}}
                    <tr style="background:#1e293b;" class="border-b-2 border-slate-600">
                        <td class="sticky left-0 z-20 px-3 py-2 text-slate-300 font-semibold text-[11px] border-r border-slate-600"
                            style="background:#1e293b;">Totals ↓</td>
                        <td class="px-2 py-2 text-center text-slate-200 font-bold" x-text="fmtInt(totalQty())"></td>
                        <td class="px-2 py-2 text-center text-slate-500">—</td>
                        <td class="px-2 py-2 text-center text-yellow-300 font-bold"  x-text="fmt(totalValue())"></td>
                        <td class="px-2 py-2 text-center text-emerald-300 font-bold" x-text="fmt(totalCash())"></td>
                        <td class="px-2 py-2 text-center text-violet-300 font-bold"  x-text="fmt(totalNlb())"></td>
                        <td class="px-2 py-2 text-center text-violet-300 font-bold"  x-text="fmt(totalDlb())"></td>
                        <td class="px-2 py-2 text-center text-purple-300 font-bold"  x-text="fmt(totalWinning())"></td>
                        <td class="px-2 py-2 text-center text-cyan-300 font-bold"    x-text="fmt(totalCW())"></td>
                        <td class="px-2 py-2 text-center text-red-300 font-bold"     x-text="fmt(Math.abs(totalBalance()))"></td>
                        <td></td>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($assistants as $i => $a)
                    @php $aid = $a->id; @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }}"
                        :class="activeRow === {{ $aid }} ? 'ring-1 ring-inset ring-blue-300 !bg-blue-50' : ''"
                        @mouseenter="activeRow = {{ $aid }}"
                        @mouseleave="activeRow = null">

                        {{-- Sticky name --}}
                        <td class="sticky left-0 z-10 px-3 py-1.5 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                            style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}"
                            :style="activeRow === {{ $aid }} ? 'background:#eff6ff' : ''">
                            <span class="mr-1 text-gray-400 text-[11px]">{{ $i + 1 }}</span>
                            {{ $a->name }}
                        </td>

                        {{-- Amount --}}
                        <td class="p-0">
                            <input type="number" min="0" step="1"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none"
                                   :value="rows[{{ $aid }}].qty || ''"
                                   @input="rows[{{ $aid }}].qty = parseInt($event.target.value) || 0">
                        </td>

                        {{-- Unit Price --}}
                        <td class="p-0">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none"
                                   :value="rows[{{ $aid }}].unitPrice"
                                   @input="rows[{{ $aid }}].unitPrice = parseFloat($event.target.value) || 0">
                        </td>

                        {{-- Value (auto) --}}
                        <td class="px-2 py-1.5 text-center font-semibold text-yellow-700 bg-yellow-50/50"
                            x-text="fmt(value({{ $aid }}))"></td>

                        {{-- Cash (opens modal) --}}
                        <td class="p-0">
                            <button type="button"
                                    class="w-full h-8 px-2 text-center text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition cursor-pointer"
                                    @click="openCashCounter({{ $aid }})"
                                    x-text="hasCash({{ $aid }}) ? fmt(cash({{ $aid }})) : '+ Count'">
                            </button>
                        </td>

                        {{-- NLB Winning --}}
                        <td class="p-0">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none"
                                   :value="rows[{{ $aid }}].nlbWinning || ''"
                                   @input="rows[{{ $aid }}].nlbWinning = parseFloat($event.target.value) || 0">
                        </td>

                        {{-- DLB Winning --}}
                        <td class="p-0">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none"
                                   :value="rows[{{ $aid }}].dlbWinning || ''"
                                   @input="rows[{{ $aid }}].dlbWinning = parseFloat($event.target.value) || 0">
                        </td>

                        {{-- TW (auto) --}}
                        <td class="px-2 py-1.5 text-center text-purple-700 font-medium bg-purple-50/30"
                            x-text="fmt(tw({{ $aid }}))"></td>

                        {{-- C+W (auto) --}}
                        <td class="px-2 py-1.5 text-center text-cyan-700 font-semibold bg-cyan-50/30"
                            x-text="fmt(cw({{ $aid }}))"></td>

                        {{-- Status --}}
                        <td class="px-1 py-1.5 text-center">
                            <template x-if="balance({{ $aid }}) > 0">
                                <div class="rounded-md bg-red-50 border border-red-200 px-1.5 py-0.5 text-center">
                                    <p class="text-[9px] font-bold text-red-500 uppercase">Outstanding</p>
                                    <p class="text-xs font-bold text-red-700" x-text="fmt(balance({{ $aid }}))"></p>
                                </div>
                            </template>
                            <template x-if="balance({{ $aid }}) < 0">
                                <div class="rounded-md bg-amber-50 border border-amber-200 px-1.5 py-0.5 text-center">
                                    <p class="text-[9px] font-bold text-amber-500 uppercase">Credit</p>
                                    <p class="text-xs font-bold text-amber-700" x-text="fmt(Math.abs(balance({{ $aid }})))"></p>
                                </div>
                            </template>
                            <template x-if="balance({{ $aid }}) === 0 && value({{ $aid }}) > 0">
                                <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700">Balanced</span>
                            </template>
                        </td>

                        {{-- Remarks --}}
                        <td class="p-0">
                            <input type="text"
                                   class="ds-cell w-full h-8 px-2 text-xs border-0 bg-transparent outline-none"
                                   :value="rows[{{ $aid }}].remarks"
                                   @input="rows[{{ $aid }}].remarks = $event.target.value"
                                   placeholder="Notes…">
                        </td>
                    </tr>
                    @endforeach

                    {{-- Bottom totals --}}
                    <tr class="border-t-2 border-gray-300 font-bold" style="background:#f1f5f9;">
                        <td class="sticky left-0 z-10 px-3 py-2.5 text-gray-700 border-r border-gray-200"
                            style="background:#f1f5f9;">Totals ↑</td>
                        <td class="px-2 py-2.5 text-center text-gray-900" x-text="fmtInt(totalQty())"></td>
                        <td class="px-2 py-2.5 text-center text-gray-400">—</td>
                        <td class="px-2 py-2.5 text-center text-yellow-700 font-bold"  x-text="fmt(totalValue())"></td>
                        <td class="px-2 py-2.5 text-center text-emerald-700 font-bold" x-text="fmt(totalCash())"></td>
                        <td class="px-2 py-2.5 text-center text-violet-700"            x-text="fmt(totalNlb())"></td>
                        <td class="px-2 py-2.5 text-center text-violet-700"            x-text="fmt(totalDlb())"></td>
                        <td class="px-2 py-2.5 text-center text-purple-700 font-bold"  x-text="fmt(totalWinning())"></td>
                        <td class="px-2 py-2.5 text-center text-cyan-700 font-bold"    x-text="fmt(totalCW())"></td>
                        <td class="px-2 py-2.5 text-center"
                            :class="totalBalance() > 0 ? 'text-red-700' : (totalBalance() < 0 ? 'text-amber-600' : 'text-green-700')"
                            x-text="fmt(Math.abs(totalBalance()))"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>

    {{-- ══════════════════════════════════════════════════════════════
         CASH COUNTER MODAL
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="cashModal.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 print:hidden"
         @click.self="cashModal.open = false"
         style="display:none;">

        <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl overflow-hidden"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal header --}}
            <div class="flex items-center justify-between bg-gray-900 px-5 py-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Cash Counter</p>
                    <p class="font-bold text-white text-sm mt-0.5"
                       x-text="cashModal.assistantId ? (names[cashModal.assistantId] ?? '') : ''"></p>
                </div>
                <button type="button" @click="cashModal.open = false"
                        class="text-gray-400 hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Denomination inputs --}}
            <div class="px-5 py-4 space-y-2.5">
                @foreach([5000 => ['bg-purple-100','text-purple-800'], 1000 => ['bg-blue-100','text-blue-800'], 500 => ['bg-green-100','text-green-800'], 100 => ['bg-yellow-100','text-yellow-800'], 50 => ['bg-orange-100','text-orange-800'], 20 => ['bg-gray-100','text-gray-700']] as $denom => $cls)
                <div class="flex items-center gap-3">
                    <span class="w-20 flex-shrink-0 rounded-full {{ $cls[0] }} {{ $cls[1] }} px-3 py-1 text-center text-xs font-bold">
                        Rs. {{ number_format($denom) }}
                    </span>
                    <span class="text-gray-400">×</span>
                    <input type="number" min="0" step="1" placeholder="0"
                           class="flex-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-center font-medium
                                  focus:outline-none focus:ring-2 focus:ring-blue-400"
                           :value="cashModal.denoms[{{ $denom }}]"
                           @input="cashModal.denoms[{{ $denom }}] = parseInt($event.target.value) || 0"
                           @keydown.enter.prevent="applyCash()">
                    <span class="w-24 flex-shrink-0 text-right text-sm font-semibold text-gray-700"
                          x-text="'Rs. ' + ((cashModal.denoms[{{ $denom }}]||0)*{{ $denom }}).toLocaleString()"></span>
                </div>
                @endforeach
            </div>

            {{-- Total + buttons --}}
            <div class="border-t border-gray-100 bg-gray-50 px-5 py-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-600">Total Cash</span>
                    <span class="text-2xl font-bold text-emerald-700"
                          x-text="'Rs. ' + fmt(cashModalTotal())"></span>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="cashModal.open = false"
                            class="flex-1 rounded-lg border border-gray-300 bg-white py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="applyCash()"
                            class="flex-1 rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /x-data --}}

{{-- ══════════════════════════════════════════════════════════════════
     DATE NAVIGATION BAR
═══════════════════════════════════════════════════════════════════ --}}
<div class="mt-5 flex gap-1.5 overflow-x-auto pb-1 print:hidden">
    @foreach($navDates as $d)
    @php $np = \Carbon\Carbon::parse($d); @endphp
    <a href="{{ route('daily-sales.index', ['date' => $d]) }}"
       class="flex-shrink-0 rounded-lg px-4 py-2 text-xs font-medium transition text-center
              {{ $d === $date
                 ? 'bg-blue-600 text-white shadow'
                 : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        <span class="block font-bold">{{ $np->format('d') }}</span>
        <span class="block opacity-75">{{ $np->format('M') }}</span>
        <span class="block" style="font-size:10px;">{{ $np->format('D') }}</span>
    </a>
    @endforeach
</div>

@endif

@push('head')
<style>
input.ds-cell::-webkit-outer-spin-button,
input.ds-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.ds-cell[type=number] { -moz-appearance: textfield; }
input.ds-cell:focus { background: #fff !important; box-shadow: inset 0 0 0 2px #3b82f6; border-radius: 2px; }
@media print { .print\:hidden { display:none !important; } aside, header { display:none !important; } }
</style>
<script>
function salesGrid(initialRows, names) {
    return {
        rows: initialRows,
        names: names,
        activeRow: null,
        cashModal: {
            open: false,
            assistantId: null,
            denoms: { 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
        },

        // ── Row computed ──────────────────────────────────────────────────────
        value(id)   { return (this.rows[id].qty||0) * (this.rows[id].unitPrice||0); },
        cash(id)    {
            const r = this.rows[id];
            return (r.d20||0)*20+(r.d50||0)*50+(r.d100||0)*100+(r.d500||0)*500+(r.d1000||0)*1000+(r.d5000||0)*5000;
        },
        hasCash(id) { const r=this.rows[id]; return (r.d20||0)+(r.d50||0)+(r.d100||0)+(r.d500||0)+(r.d1000||0)+(r.d5000||0)>0; },
        tw(id)      { return (this.rows[id].nlbWinning||0)+(this.rows[id].dlbWinning||0); },
        cw(id)      { return this.cash(id)+this.tw(id); },
        balance(id) { return this.value(id)-this.cw(id); },

        // ── Day totals ────────────────────────────────────────────────────────
        _ids()         { return Object.keys(this.rows); },
        totalQty()     { return this._ids().reduce((s,id)=>s+(parseInt(this.rows[id].qty)||0),0); },
        totalValue()   { return this._ids().reduce((s,id)=>s+this.value(id),0); },
        totalCash()    { return this._ids().reduce((s,id)=>s+this.cash(id),0); },
        totalNlb()     { return this._ids().reduce((s,id)=>s+(parseFloat(this.rows[id].nlbWinning)||0),0); },
        totalDlb()     { return this._ids().reduce((s,id)=>s+(parseFloat(this.rows[id].dlbWinning)||0),0); },
        totalWinning() { return this._ids().reduce((s,id)=>s+this.tw(id),0); },
        totalCW()      { return this._ids().reduce((s,id)=>s+this.cw(id),0); },
        totalBalance() { return this._ids().reduce((s,id)=>s+this.balance(id),0); },

        // ── Cash Counter Modal ────────────────────────────────────────────────
        openCashCounter(id) {
            const r = this.rows[id];
            this.cashModal.denoms = { 20:r.d20||0, 50:r.d50||0, 100:r.d100||0, 500:r.d500||0, 1000:r.d1000||0, 5000:r.d5000||0 };
            this.cashModal.assistantId = id;
            this.cashModal.open = true;
        },
        cashModalTotal() {
            const d = this.cashModal.denoms;
            return (d[20]||0)*20+(d[50]||0)*50+(d[100]||0)*100+(d[500]||0)*500+(d[1000]||0)*1000+(d[5000]||0)*5000;
        },
        applyCash() {
            const id = this.cashModal.assistantId;
            const d  = this.cashModal.denoms;
            Object.assign(this.rows[id], { d20:d[20]||0, d50:d[50]||0, d100:d[100]||0, d500:d[500]||0, d1000:d[1000]||0, d5000:d[5000]||0 });
            this.cashModal.open = false;
        },

        // ── Formatters ────────────────────────────────────────────────────────
        fmt(n)    { return Number(n||0).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0}); },
        fmtInt(n) { return Number(n||0).toLocaleString(); },
    };
}
</script>
@endpush

</x-layouts.app>
