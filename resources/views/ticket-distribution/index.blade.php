<x-layouts.app title="Ticket Distribution">

@php
    $parsedDate  = \Carbon\Carbon::parse($date);
    $prevDate    = $parsedDate->copy()->subDay()->toDateString();
    $nextDate    = $parsedDate->copy()->addDay()->toDateString();
    $isToday     = $parsedDate->isToday();
    // Build Alpine initial grid state from PHP
    $alpineGrid = [];
    foreach ($assistants as $a) {
        $alpineGrid[$a->id] = [];
        foreach ($lotteries as $l) {
            $alpineGrid[$a->id][$l->id] = $grid[$a->id][$l->id] ?? 0;
        }
    }
@endphp

{{-- ── Top bar ──────────────────────────────────────────────────────────────── --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">

    {{-- Date navigation --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('ticket-distribution.index', ['date' => $prevDate]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">‹</a>

        <input type="date"
               id="date-picker"
               value="{{ $date }}"
               onchange="window.location.href = '{{ route('ticket-distribution.index') }}?date=' + this.value"
               class="erp-input text-sm h-9 font-semibold">

        <a href="{{ route('ticket-distribution.index', ['date' => $nextDate]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50 {{ $isToday ? 'opacity-40 pointer-events-none' : '' }}">›</a>

        <span class="text-sm font-semibold text-gray-700">{{ $parsedDate->format('l') }}</span>
        @if($isToday)
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Today</span>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('ticket-distribution.summary') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Summary
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        {{-- Adjustment Mode Toggle (bound to Alpine scope below) --}}
        <button id="adj-mode-btn"
                onclick="toggleAdjustmentMode()"
                class="inline-flex items-center gap-1.5 rounded-lg border px-4 py-2 text-sm font-medium transition"
                style="border-color:#d97706; color:#92400e; background:#fffbeb;">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span id="adj-mode-label">Adjustment Mode</span>
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700 print:hidden">
        {{ session('success') }}
    </div>
@endif

@if($lotteries->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
        <p class="text-sm text-gray-500">No lotteries configured yet.</p>
        <a href="{{ route('lotteries.create') }}" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:underline">Add lotteries →</a>
    </div>
@elseif($assistants->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
        <p class="text-sm text-gray-500">No sales assistants yet.</p>
        <a href="{{ route('assistants.create') }}" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:underline">Add assistants →</a>
    </div>
@else

{{-- ══════════════════════════════════════════════════════════════════════════
     Alpine.js Data-Entry Grid
     grid[assistantId][lotteryId] = qty (integer)
     All totals computed reactively.
══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="distGrid({{ json_encode($alpineGrid) }}, {{ json_encode($lotteries->pluck('id')->values()) }}, {{ json_encode($assistants->pluck('id')->values()) }})"
     @keydown.window="handleArrow($event)"
     x-init="$watch('adjustmentMode', v => syncAdjustmentModeUI(v))"
     id="dist-grid-root"
     @keydown.escape="adjustmentMode && (adjustmentMode = false)">

    <form id="dist-form" method="POST" action="{{ route('ticket-distribution.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        {{-- Hidden inputs synced from Alpine --}}
        @foreach($assistants as $a)
            @foreach($lotteries as $l)
                <input type="hidden"
                       :name="`qty[{{ $a->id }}][{{ $l->id }}]`"
                       :value="grid[{{ $a->id }}][{{ $l->id }}] || 0">
            @endforeach
        @endforeach

        {{-- Save button bar --}}
        <div class="mb-3 flex items-center justify-between print:hidden">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">
                    {{ $parsedDate->format('Y-m-d') }} — {{ $parsedDate->format('l') }}
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-0.5 text-xs font-bold text-gray-600">
                    Grand Total: <span x-text="grandTotal().toLocaleString()" class="text-blue-700"></span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                {{-- Auto-Adjust button — visible only in adjustment mode --}}
                <button type="button"
                        x-show="adjustmentMode"
                        x-cloak
                        @click="autoAdjust()"
                        class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white shadow transition"
                        style="background:#d97706;">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Auto-Adjust Distribution
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Distribution
                </button>
            </div>
        </div>

        {{-- Adjustment Mode info banner --}}
        <div x-show="adjustmentMode"
             x-cloak
             class="mb-3 flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm text-amber-800 print:hidden">
            <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
            </svg>
            <span><strong>Adjustment Mode is ON.</strong> Enter Board Received Qty in the amber row, lock any assistant you don't want touched, then click <em>Auto-Adjust Distribution</em>. Amber cells were auto-adjusted. Press <kbd class="rounded bg-amber-200 px-1 font-mono text-xs">Esc</kbd> to exit.</span>
        </div>

        {{-- ── Scrollable grid ──────────────────────────────────────────── --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full border-collapse text-xs" id="dist-table">

                <thead>
                    <tr style="background:#0f172a;">
                        <th class="sticky left-0 z-20 px-3 py-3 text-left text-white font-medium whitespace-nowrap border-r border-slate-700"
                            style="background:#0f172a; min-width:150px;">
                            # &nbsp; Assistant
                        </th>
                        @foreach($lotteries as $l)
                            <th class="px-2 py-3 text-center font-medium whitespace-nowrap
                                       {{ $l->board === 'NLB' ? 'text-blue-300' : 'text-orange-300' }}"
                                style="min-width:62px;">
                                {{ $l->name }}
                                <span class="block text-gray-500 font-normal" style="font-size:10px;">
                                    Rs.{{ number_format($l->unit_price, 0) }}
                                </span>
                            </th>
                        @endforeach
                        <th class="px-3 py-3 text-center text-yellow-300 font-semibold whitespace-nowrap">
                            Total
                        </th>
                        <th x-show="adjustmentMode" x-cloak
                            class="px-2 py-3 text-center text-amber-400 font-semibold whitespace-nowrap text-[11px]"
                            style="min-width:52px;">Lock</th>
                    </tr>

                    {{-- ── BOARD RECEIVED QTY ROW (Adjustment Mode only) ─── --}}
                    <tr x-show="adjustmentMode" x-cloak
                        style="background:#451a03;"
                        class="border-b border-amber-800">
                        <td class="sticky left-0 z-20 px-3 py-2 font-semibold text-[11px] border-r border-amber-800 whitespace-nowrap"
                            style="background:#451a03; color:#fbbf24;">
                            Board Received Qty
                        </td>
                        @foreach($lotteries as $l)
                            <td class="p-0">
                                <input type="number" min="0" step="1"
                                       class="board-qty-cell w-full h-8 px-1 text-center text-xs font-bold border-0 outline-none focus:ring-1 focus:ring-amber-400 focus:z-10 relative"
                                       style="background:#78350f; color:#fef3c7;"
                                       :value="boardQty[{{ $l->id }}] || ''"
                                       @input="boardQty[{{ $l->id }}] = parseInt($event.target.value) || 0"
                                       placeholder="—">
                            </td>
                        @endforeach
                        <td class="px-3 py-2 text-center text-amber-300 font-bold text-xs"
                            x-text="boardGrandTotal().toLocaleString()"></td>
                    </tr>

                    <tr class="border-b-2 border-slate-600" style="background:#1e293b;">
                        <td class="sticky left-0 z-20 px-3 py-2 text-slate-300 font-semibold text-[11px] border-r border-slate-600"
                            style="background:#1e293b;">Column Total ↓</td>
                        @foreach($lotteries as $l)
                            <td class="px-2 py-2 text-center font-bold"
                                x-bind:class="adjustmentMode && boardQty[{{ $l->id }}] > 0 && boardQty[{{ $l->id }}] !== colTotal({{ $l->id }}) ? 'text-amber-400' : 'text-slate-100'"
                                x-text="colTotal({{ $l->id }}).toLocaleString()"></td>
                        @endforeach
                        <td class="px-3 py-2 text-center text-yellow-300 font-bold"
                            x-text="grandTotal().toLocaleString()"></td>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($assistants as $i => $a)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/60' }}"
                            x-bind:class="hoveredRow === {{ $a->id }} ? 'ring-1 ring-inset ring-blue-300 bg-blue-50' : ''"
                            @mouseenter="hoveredRow = {{ $a->id }}"
                            @mouseleave="hoveredRow = null">

                            <td class="sticky left-0 z-10 px-3 py-1.5 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                                style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}"
                                x-bind:style="hoveredRow === {{ $a->id }} ? 'background:#eff6ff' : ''">
                                <span class="mr-1.5 text-gray-400 text-[11px]">{{ $i + 1 }}</span>
                                {{ $a->name }}
                            </td>

                            @foreach($lotteries as $j => $l)
                                <td class="p-0 relative"
                                    x-bind:class="{
                                        'bg-blue-50': hoveredCol === {{ $l->id }} && !adjusted[{{ $a->id }}][{{ $l->id }}],
                                        'bg-amber-100': adjusted[{{ $a->id }}][{{ $l->id }}]
                                    }">
                                    <input
                                        type="number" min="0" step="1"
                                        class="dist-cell w-full h-8 px-1 text-center text-xs font-medium border-0 bg-transparent focus:ring-1 focus:ring-blue-400 focus:z-10 relative outline-none"
                                        :class="adjusted[{{ $a->id }}][{{ $l->id }}] ? 'text-amber-800 font-bold' : ''"
                                        :style="adjusted[{{ $a->id }}][{{ $l->id }}] ? 'background:transparent' : ''"
                                        :value="grid[{{ $a->id }}][{{ $l->id }}] || ''"
                                        @focus="hoveredCol = {{ $l->id }}; adjusted[{{ $a->id }}][{{ $l->id }}] = false"
                                        @blur="hoveredCol = null"
                                        @input="grid[{{ $a->id }}][{{ $l->id }}] = parseInt($event.target.value) || 0; adjusted[{{ $a->id }}][{{ $l->id }}] = false"
                                        data-row="{{ $i }}"
                                        data-col="{{ $j }}"
                                        placeholder="">
                                </td>
                            @endforeach

                            <td class="px-3 py-1.5 text-center font-bold"
                                x-bind:class="rowTotal({{ $a->id }}) > 0 ? 'text-blue-700' : 'text-gray-300'"
                                x-text="rowTotal({{ $a->id }}).toLocaleString()"></td>

                            {{-- Lock button (adjustment mode only) --}}
                            <td x-show="adjustmentMode" x-cloak class="px-1 py-1 text-center">
                                <button type="button"
                                        @click="locked[{{ $a->id }}] = !locked[{{ $a->id }}]"
                                        :title="locked[{{ $a->id }}] ? 'Locked — click to unlock' : 'Click to lock this row'"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-md border transition"
                                        :class="locked[{{ $a->id }}] ? 'border-amber-500 bg-amber-100 text-amber-700' : 'border-gray-300 bg-white text-gray-400 hover:border-amber-400 hover:text-amber-600'">
                                    <svg x-show="!locked[{{ $a->id }}]" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4M5 11h14a1 1 0 011 1v8a1 1 0 01-1 1H5a1 1 0 01-1-1v-8a1 1 0 011-1z"/>
                                    </svg>
                                    <svg x-show="locked[{{ $a->id }}]" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 1a4 4 0 00-4 4v4H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2h-3V5a4 4 0 00-4-4zm0 2a2 2 0 012 2v4h-4V5a2 2 0 012-2zm0 9a2 2 0 110 4 2 2 0 010-4z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="border-t-2 border-gray-300 font-bold" style="background:#f1f5f9;">
                        <td class="sticky left-0 z-10 px-3 py-2.5 text-gray-700 border-r border-gray-200"
                            style="background:#f1f5f9;">Column Total ↑</td>
                        @foreach($lotteries as $l)
                            <td class="px-2 py-2.5 text-center"
                                x-bind:class="adjustmentMode && boardQty[{{ $l->id }}] > 0 && boardQty[{{ $l->id }}] !== colTotal({{ $l->id }}) ? 'text-amber-600' : 'text-gray-900'"
                                x-text="colTotal({{ $l->id }}).toLocaleString()"></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center text-blue-700"
                            x-text="grandTotal().toLocaleString()"></td>
                        <td x-show="adjustmentMode" x-cloak></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>
</div>

{{-- Legend --}}
<p class="mt-2 text-xs text-gray-400 print:hidden">
    <span class="inline-block w-3 h-3 rounded-sm bg-emerald-100 border border-emerald-300 mr-1"></span>
    Green values were pre-filled from saved defaults for this day of the week.
</p>

@endif

@push('head')
<style>
/* Remove number input spinners for cleaner grid cells */
input.dist-cell::-webkit-outer-spin-button,
input.dist-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.dist-cell[type=number] { -moz-appearance: textfield; }
input.dist-cell::placeholder { color: #d1d5db; }
input.dist-cell:focus { background: #fff; box-shadow: inset 0 0 0 2px #3b82f6; border-radius: 2px; }

/* Board Received Qty input cells */
input.board-qty-cell::-webkit-outer-spin-button,
input.board-qty-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.board-qty-cell[type=number] { -moz-appearance: textfield; }
input.board-qty-cell::placeholder { color: #92400e; opacity: 0.6; }
input.board-qty-cell:focus { box-shadow: inset 0 0 0 2px #f59e0b; border-radius: 2px; }

/* Amber adjusted cell pulse on first appearance */
@keyframes adj-flash { from { background: #fde68a; } to { background: #fef3c7; } }
.bg-amber-100 { animation: adj-flash 0.4s ease-out; }

/* [x-cloak] hides elements before Alpine initialises */
[x-cloak] { display: none !important; }

@media print {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;

    /* Hide all screen-only UI */
    .print\:hidden { display: none !important; }
    aside, header, nav, [class*="sidebar"] { display: none !important; }
    body, html { margin: 0 !important; padding: 0 !important; }

    /* Hide the Alpine interactive grid */
    #dist-form  { display: none !important; }

    /* Show the static PHP-rendered print tables */
    #print-tables { display: block !important; }

    /* Ensure page breaks work correctly */
    @page { margin: 10mm; size: A4 landscape; }
}
</style>

<script>
function distGrid(initialGrid, lotteryIds, assistantIds) {
    // Build initial adjusted and boardQty maps
    const initAdjusted = {};
    const initLocked   = {};
    assistantIds.forEach(aid => {
        initAdjusted[aid] = {};
        initLocked[aid]   = false;
        lotteryIds.forEach(lid => { initAdjusted[aid][lid] = false; });
    });
    const initBoardQty = {};
    lotteryIds.forEach(lid => { initBoardQty[lid] = 0; });

    return {
        grid:           initialGrid,
        hoveredRow:     null,
        hoveredCol:     null,
        adjustmentMode: false,
        boardQty:       initBoardQty,
        locked:         initLocked,
        adjusted:       initAdjusted,

        clearGrid() {
            for (const aId of Object.keys(this.grid)) {
                for (const lId of Object.keys(this.grid[aId])) {
                    this.grid[aId][lId] = 0;
                }
            }
            this.defaultsGrid    = {};
            this.defaultsApplied = false;
        },

        gridIsEmpty() { return this.grandTotal() === 0; },

        // ── Totals ─────────────────────────────────────────────────────────
        rowTotal(assistantId) {
            const row = this.grid[assistantId] ?? {};
            return Object.values(row).reduce((s, v) => s + (parseInt(v) || 0), 0);
        },

        colTotal(lotteryId) {
            return Object.values(this.grid).reduce((s, row) => s + (parseInt(row[lotteryId]) || 0), 0);
        },

        grandTotal() {
            return Object.values(this.grid).reduce((s, row) =>
                s + Object.values(row).reduce((rs, v) => rs + (parseInt(v) || 0), 0), 0);
        },

        // ── Form submission ────────────────────────────────────────────────
        async submitForm() {
            this.saving = true;
            try {
                if (this.saveAsDefault) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response  = await fetch(saveDefaultsUrl, {
                        method:  'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'Accept':           'application/json',
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ date: currentDate, qty: this.grid }),
                    });
                    if (!response.ok) throw new Error('Server error ' + response.status);
                }
                document.getElementById('dist-form').submit();
            } catch (err) {
                this.saving = false;
                alert('Failed to save defaults. Please try again.');
            }
        },

        boardGrandTotal() {
            return lotteryIds.reduce((s, lid) => s + (parseInt(this.boardQty[lid]) || 0), 0);
        },

        // ── Proportional auto-adjust ───────────────────────────────────────
        autoAdjust() {
            lotteryIds.forEach(lid => {
                const target = parseInt(this.boardQty[lid]) || 0;
                if (target <= 0) return;           // skip: no board qty entered

                const current = this.colTotal(lid);
                let delta = target - current;
                if (delta === 0) return;            // already matches

                // Collect unlocked assistants
                const unlocked = assistantIds.filter(aid => !this.locked[aid]);
                if (unlocked.length === 0) return;  // all locked

                // Quantities for each unlocked assistant
                const qtys = {};
                unlocked.forEach(aid => { qtys[aid] = parseInt(this.grid[aid][lid]) || 0; });
                const totalUnlocked = unlocked.reduce((s, aid) => s + qtys[aid], 0);

                let allocations;

                if (totalUnlocked === 0) {
                    // All unlocked assistants have 0 — can only distribute an increase equally
                    if (delta <= 0) return;
                    allocations = {};
                    unlocked.forEach(aid => { allocations[aid] = 0; });
                    let rem = delta;
                    let i   = 0;
                    while (rem > 0) {
                        allocations[unlocked[i % unlocked.length]]++;
                        rem--; i++;
                    }
                } else {
                    // Proportional share with largest-remainder rounding
                    const shares = unlocked.map(aid => {
                        const exact = delta * (qtys[aid] / totalUnlocked);
                        return { aid, exact, floor: Math.trunc(exact), rem: Math.abs(exact - Math.trunc(exact)) };
                    });

                    let distributed = shares.reduce((s, x) => s + x.floor, 0);
                    let leftover    = delta - distributed;                   // may be ±
                    const step      = delta > 0 ? 1 : -1;

                    // Give remaining units to those with biggest fractional remainders
                    shares.sort((a, b) => b.rem - a.rem);
                    for (let i = 0; Math.abs(leftover) > 0 && i < shares.length; i++) {
                        shares[i].floor += step;
                        leftover        -= step;
                    }

                    allocations = {};
                    shares.forEach(({ aid, floor }) => { allocations[aid] = floor; });
                }

                // Apply, clamping to 0 (never negative), then shift remainder to others
                let unspent = 0;
                unlocked.forEach(aid => {
                    const newQty = qtys[aid] + (allocations[aid] || 0);
                    if (newQty < 0) {
                        unspent += newQty;          // negative remainder to redistribute
                        this.grid[aid][lid] = 0;
                        if (qtys[aid] !== 0) this.adjusted[aid][lid] = true;
                    } else {
                        this.grid[aid][lid] = newQty;
                        if (newQty !== qtys[aid]) this.adjusted[aid][lid] = true;
                    }
                });

                // Redistribute any unspent (clamped) remainder to unlocked assistants with qty > 0
                if (unspent !== 0) {
                    const candidates = unlocked.filter(aid => (parseInt(this.grid[aid][lid]) || 0) > 0);
                    candidates.forEach(aid => {
                        if (unspent === 0) return;
                        const step2  = unspent > 0 ? 1 : -1;
                        const newQty = (parseInt(this.grid[aid][lid]) || 0) + step2;
                        if (newQty >= 0) {
                            this.grid[aid][lid] = newQty;
                            this.adjusted[aid][lid] = true;
                            unspent -= step2;
                        }
                    });
                }

                // Force Alpine to re-render grid by creating a fresh shallow copy
                this.grid = Object.assign({}, this.grid);
            });
        },

        handleArrow(e) {
            const el = document.activeElement;
            if (!el || !el.classList.contains('dist-cell')) return;

            const row = parseInt(el.dataset.row);
            const col = parseInt(el.dataset.col);
            const totalRows = {{ count($assistants) }};
            const totalCols = {{ count($lotteries) }};
            let targetRow = row, targetCol = col;

            if (e.key === 'ArrowRight' || e.key === 'Tab' && !e.shiftKey) {
                if (col < totalCols - 1) { targetCol = col + 1; }
                else if (row < totalRows - 1) { targetRow = row + 1; targetCol = 0; }
            } else if (e.key === 'ArrowLeft' || e.key === 'Tab' && e.shiftKey) {
                if (col > 0) { targetCol = col - 1; }
                else if (row > 0) { targetRow = row - 1; targetCol = totalCols - 1; }
            } else if (e.key === 'ArrowDown' || e.key === 'Enter') {
                if (row < totalRows - 1) targetRow = row + 1;
            } else if (e.key === 'ArrowUp') {
                if (row > 0) targetRow = row - 1;
            } else {
                return;
            }

            if (e.key !== 'Tab') e.preventDefault();

            const next = document.querySelector(
                `.dist-cell[data-row="${targetRow}"][data-col="${targetCol}"]`
            );
            if (next) { next.focus(); next.select(); }
        }
    };
}

// Toggle adjustment mode from the external button (outside Alpine scope)
function toggleAdjustmentMode() {
    const root = document.getElementById('dist-grid-root');
    if (!root || !root._x_dataStack) return;
    const data = Alpine.$data(root);
    data.adjustmentMode = !data.adjustmentMode;
}

function syncAdjustmentModeUI(active) {
    const btn   = document.getElementById('adj-mode-btn');
    const label = document.getElementById('adj-mode-label');
    if (!btn || !label) return;
    if (active) {
        btn.style.background     = '#d97706';
        btn.style.borderColor    = '#b45309';
        btn.style.color          = '#fff';
        label.textContent        = 'Exit Adjustment Mode';
    } else {
        btn.style.background     = '#fffbeb';
        btn.style.borderColor    = '#d97706';
        btn.style.color          = '#92400e';
        label.textContent        = 'Adjustment Mode';
    }
}
</script>
@endpush

</x-layouts.app>