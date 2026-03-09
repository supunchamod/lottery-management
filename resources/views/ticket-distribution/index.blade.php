<x-layouts.app title="Ticket Distribution">

@php
    use Carbon\Carbon;
    $parsedDate  = Carbon::parse($date);
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

        <form method="GET" action="{{ route('ticket-distribution.index') }}">
            <input type="date" name="date" value="{{ $date }}"
                   onchange="this.form.submit()"
                   class="erp-input text-sm h-9 font-semibold">
        </form>

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
<div x-data="distGrid({{ json_encode($alpineGrid) }})"
     @keydown.window="handleArrow($event)">

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

        {{-- Save button (sticky top-right) --}}
        <div class="mb-3 flex items-center justify-between print:hidden">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">
                    {{ $parsedDate->format('Y-m-d') }} — {{ $parsedDate->format('l') }}
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-0.5 text-xs font-bold text-gray-600">
                    Grand Total: <span x-text="grandTotal().toLocaleString()" class="text-blue-700"></span>
                </span>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Distribution
            </button>
        </div>

        {{-- Scrollable grid --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full border-collapse text-xs" id="dist-table">

                {{-- ── HEADER ─────────────────────────────────────────────── --}}
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
                    </tr>

                    {{-- ── TOP TOTALS ROW ──────────────────────────────────── --}}
                    <tr class="border-b-2 border-slate-600" style="background:#1e293b;">
                        <td class="sticky left-0 z-20 px-3 py-2 text-slate-300 font-semibold text-[11px] border-r border-slate-600"
                            style="background:#1e293b;">Column Total ↓</td>
                        @foreach($lotteries as $l)
                            <td class="px-2 py-2 text-center text-slate-100 font-bold"
                                x-text="colTotal({{ $l->id }}).toLocaleString()"></td>
                        @endforeach
                        <td class="px-3 py-2 text-center text-yellow-300 font-bold"
                            x-text="grandTotal().toLocaleString()"></td>
                    </tr>
                </thead>

                {{-- ── BODY ────────────────────────────────────────────────── --}}
                <tbody class="divide-y divide-gray-100">
                    @foreach($assistants as $i => $a)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/60' }}"
                            x-bind:class="hoveredRow === {{ $a->id }} ? 'ring-1 ring-inset ring-blue-300 bg-blue-50' : ''"
                            @mouseenter="hoveredRow = {{ $a->id }}"
                            @mouseleave="hoveredRow = null">

                            {{-- Sticky name cell --}}
                            <td class="sticky left-0 z-10 px-3 py-1.5 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                                style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}"
                                x-bind:style="hoveredRow === {{ $a->id }} ? 'background:#eff6ff' : ''">
                                <span class="mr-1.5 text-gray-400 text-[11px]">{{ $i + 1 }}</span>
                                {{ $a->name }}
                            </td>

                            {{-- Input cells --}}
                            @foreach($lotteries as $j => $l)
                                <td class="p-0 relative"
                                    x-bind:class="hoveredCol === {{ $l->id }} ? 'bg-blue-50' : ''">
                                    <input
                                        type="number" min="0" step="1"
                                        class="dist-cell w-full h-8 px-1 text-center text-xs font-medium border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 focus:z-10 relative outline-none"
                                        :value="grid[{{ $a->id }}][{{ $l->id }}] || ''"
                                        @focus="hoveredCol = {{ $l->id }}"
                                        @blur="hoveredCol = null"
                                        @input="grid[{{ $a->id }}][{{ $l->id }}] = parseInt($event.target.value) || 0"
                                        data-row="{{ $i }}"
                                        data-col="{{ $j }}"
                                        placeholder="">
                                </td>
                            @endforeach

                            {{-- Row total --}}
                            <td class="px-3 py-1.5 text-center font-bold"
                                x-bind:class="rowTotal({{ $a->id }}) > 0 ? 'text-blue-700' : 'text-gray-300'"
                                x-text="rowTotal({{ $a->id }}).toLocaleString()"></td>
                        </tr>
                    @endforeach

                    {{-- ── BOTTOM TOTALS ROW ───────────────────────────────── --}}
                    <tr class="border-t-2 border-gray-300 font-bold" style="background:#f1f5f9;">
                        <td class="sticky left-0 z-10 px-3 py-2.5 text-gray-700 border-r border-gray-200"
                            style="background:#f1f5f9;">Column Total ↑</td>
                        @foreach($lotteries as $l)
                            <td class="px-2 py-2.5 text-center text-gray-900"
                                x-text="colTotal({{ $l->id }}).toLocaleString()"></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center text-blue-700"
                            x-text="grandTotal().toLocaleString()"></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>
</div>

@endif

@push('head')
<style>
/* Remove number input spinners for cleaner grid cells */
input.dist-cell::-webkit-outer-spin-button,
input.dist-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.dist-cell[type=number] { -moz-appearance: textfield; }

input.dist-cell::placeholder { color: #d1d5db; }
input.dist-cell:focus { background: #fff; box-shadow: inset 0 0 0 2px #3b82f6; border-radius: 2px; }

@media print {
    .print\:hidden { display: none !important; }
    aside, header, nav { display: none !important; }
    .overflow-x-auto { overflow: visible !important; }
    input.dist-cell { border: none; }
}
</style>

<script>
function distGrid(initialGrid) {
    return {
        grid: initialGrid,
        hoveredRow: null,
        hoveredCol: null,

        rowTotal(assistantId) {
            const row = this.grid[assistantId] ?? {};
            return Object.values(row).reduce((s, v) => s + (parseInt(v) || 0), 0);
        },

        colTotal(lotteryId) {
            return Object.values(this.grid).reduce((s, row) => {
                return s + (parseInt(row[lotteryId]) || 0);
            }, 0);
        },

        grandTotal() {
            return Object.values(this.grid).reduce((s, row) => {
                return s + Object.values(row).reduce((rs, v) => rs + (parseInt(v) || 0), 0);
            }, 0);
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
</script>
@endpush

</x-layouts.app>
