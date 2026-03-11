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
     PRINT-ONLY STATIC TABLES — hidden on screen, visible on print
     26 assistants per page, pure PHP (no Alpine dependency)
     ══════════════════════════════════════════════════════════════════════════ --}}
@php $chunks = $assistants->chunk(26); $totalPages = $chunks->count(); @endphp

<div id="print-tables" style="display:none;">
    @foreach($chunks as $pageNum => $chunk)
    <div style="page-break-after: {{ $loop->last ? 'auto' : 'always' }}; padding: 10px; font-family: Arial, sans-serif;">

        {{-- Page header --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; border-bottom:2px solid #0f172a; padding-bottom:6px;">
            <div>
                <div style="font-size:13pt; font-weight:bold; color:#0f172a;">Ticket Distribution</div>
                <div style="font-size:10pt; color:#475569; margin-top:2px;">{{ $parsedDate->format('l, d F Y') }}</div>
            </div>
            <div style="text-align:right; font-size:8.5pt; color:#64748b; line-height:1.6;">
                <div>Page {{ $pageNum + 1 }} of {{ $totalPages }}</div>
                <div>Grand Total: <strong style="color:#1d4ed8;">{{ number_format($grandTotal) }}</strong></div>
                <div>Printed: {{ now()->format('d M Y H:i') }}</div>
            </div>
        </div>

        {{-- Table --}}
        <table style="width:100%; border-collapse:collapse; font-size:8pt;">
            <thead>
                {{-- Lottery header --}}
                <tr style="background:#0f172a;">
                    <th style="padding:5px 6px; text-align:left; color:white; white-space:nowrap; min-width:130px; border-right:1px solid #334155;">
                        # &nbsp; Assistant
                    </th>
                    @foreach($lotteries as $l)
                        <th style="padding:5px 3px; text-align:center; white-space:nowrap; min-width:42px;
                                   color:{{ $l->board === 'NLB' ? '#93c5fd' : '#fdba74' }};">
                            {{ $l->name }}
                            <span style="display:block; font-size:7pt; color:#94a3b8; font-weight:normal;">
                                Rs.{{ number_format($l->unit_price, 0) }}
                            </span>
                        </th>
                    @endforeach
                    <th style="padding:5px 6px; text-align:center; color:#fde68a; white-space:nowrap;">
                        Total
                    </th>
                </tr>

                {{-- Column totals — top --}}
                <tr style="background:#1e293b; border-bottom:2px solid #475569;">
                    <td style="padding:4px 6px; color:#94a3b8; font-size:7.5pt; font-weight:600; border-right:1px solid #475569;">
                        Col. Total ↓
                    </td>
                    @foreach($lotteries as $l)
                        <td style="padding:4px 3px; text-align:center; color:#f1f5f9; font-weight:bold;">
                            {{ $colTotals[$l->id] > 0 ? number_format($colTotals[$l->id]) : '—' }}
                        </td>
                    @endforeach
                    <td style="padding:4px 6px; text-align:center; color:#fde68a; font-weight:bold;">
                        {{ number_format($grandTotal) }}
                    </td>
                </tr>
            </thead>

            <tbody>
                @foreach($chunk as $i => $a)
                    @php
                        $globalIndex = $pageNum * 26 + $i;
                        $bg = $i % 2 === 0 ? '#ffffff' : '#f8fafc';
                        $rowTot = $rowTotals[$a->id] ?? 0;
                    @endphp
                    <tr style="background:{{ $bg }}; border-bottom:1px solid #e2e8f0;">
                        <td style="padding:4px 6px; font-weight:500; color:#1e293b; white-space:nowrap; border-right:1px solid #e2e8f0;">
                            <span style="color:#94a3b8; font-size:7.5pt; margin-right:4px;">{{ $globalIndex + 1 }}</span>
                            {{ $a->name }}
                        </td>
                        @foreach($lotteries as $l)
                            @php $qty = $grid[$a->id][$l->id] ?? 0; @endphp
                            <td style="padding:4px 3px; text-align:center;
                                       font-weight:{{ $qty > 0 ? '600' : '400' }};
                                       color:{{ $qty > 0 ? '#1e40af' : '#cbd5e1' }};">
                                {{ $qty > 0 ? number_format($qty) : '—' }}
                            </td>
                        @endforeach
                        <td style="padding:4px 6px; text-align:center; font-weight:bold;
                                   color:{{ $rowTot > 0 ? '#1d4ed8' : '#cbd5e1' }};">
                            {{ $rowTot > 0 ? number_format($rowTot) : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- Column totals — bottom --}}
            <tfoot>
                <tr style="background:#f1f5f9; border-top:2px solid #94a3b8;">
                    <td style="padding:5px 6px; font-weight:bold; color:#374151; border-right:1px solid #cbd5e1;">
                        Col. Total ↑
                    </td>
                    @foreach($lotteries as $l)
                        <td style="padding:5px 3px; text-align:center; font-weight:bold; color:#111827;">
                            {{ $colTotals[$l->id] > 0 ? number_format($colTotals[$l->id]) : '—' }}
                        </td>
                    @endforeach
                    <td style="padding:5px 6px; text-align:center; font-weight:bold; color:#1d4ed8;">
                        {{ number_format($grandTotal) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Page footer --}}
        <div style="margin-top:6px; font-size:7.5pt; color:#94a3b8; display:flex; justify-content:space-between;">
            <span>Assistants {{ $pageNum * 26 + 1 }}–{{ min(($pageNum + 1) * 26, $assistants->count()) }} of {{ $assistants->count() }}</span>
            <span>{{ config('app.name') }} — Confidential</span>
        </div>

    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     Alpine.js Data-Entry Grid — with Smart Default Quantity
     ══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="distGrid(
        {{ json_encode($alpineGrid) }},
        '{{ $date }}',
        '{{ route('api.ticket-distribution.defaults.get') }}',
        '{{ route('api.ticket-distribution.defaults.save') }}'
     )"
     x-init="init()"
     @keydown.window="handleArrow($event)"
     class="print:hidden">

    {{-- ── Smart Default banner ──────────────────────────────────────────── --}}
    <div class="mb-3">

        <div x-show="defaultsLoading"
             x-transition
             class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Loading smart defaults for <span x-text="dayName" class="font-semibold ml-1"></span>…
        </div>

        <div x-show="defaultsApplied && !defaultsLoading"
             x-transition
             class="flex items-center justify-between gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            <span>
                <svg class="inline h-4 w-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Smart defaults pre-filled for <strong x-text="dayName"></strong>. Change any value or click Save.
            </span>
            <button type="button" @click="clearGrid()" class="text-xs text-emerald-700 underline hover:no-underline">
                Clear all
            </button>
        </div>

        <div x-show="defaultsChecked && !defaultsApplied && !defaultsLoading && gridIsEmpty()"
             x-transition
             class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            No defaults saved for <span x-text="dayName" class="font-semibold mx-1"></span> yet. Fill in the grid and check <em>Save as Default</em> before saving.
        </div>

    </div>

    {{-- ── Adjustment Mode Panel ─────────────────────────────────────────── --}}
    <div x-show="adjustMode"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="mb-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-2.5 min-w-0">
                <svg class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Adjustment Mode Active</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Enter the actual quantities received from the Board in the <strong>Board Received Qty</strong> row above the Column Total.
                        Click <strong>Auto-Adjust</strong> to proportionally redistribute. Use the
                        <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                        lock icon on any assistant's row to protect their allocation.
                        <span class="inline-block w-3 h-3 rounded-sm bg-amber-200 border border-amber-400 align-middle mx-0.5"></span>
                        Amber cells indicate auto-adjusted values.
                    </p>
                </div>
            </div>
            <button type="button"
                    @click="autoAdjust()"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 active:bg-amber-700 transition whitespace-nowrap shrink-0">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Auto-Adjust Distribution
            </button>
        </div>
    </div>

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

        {{-- ── Toolbar ──────────────────────────────────────────────────── --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">
                    {{ $parsedDate->format('Y-m-d') }} — {{ $parsedDate->format('l') }}
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-0.5 text-xs font-bold text-gray-600">
                    Grand Total: <span x-text="grandTotal().toLocaleString()" class="text-blue-700"></span>
                </span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Adjustment Mode Toggle --}}
                <button type="button"
                        @click="toggleAdjustMode()"
                        :class="adjustMode
                            ? 'bg-amber-100 border-amber-400 text-amber-800 hover:bg-amber-200'
                            : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                        title="Toggle adjustment mode to reconcile Board received quantities">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                    </svg>
                    <span x-text="adjustMode ? 'Exit Adjust Mode' : 'Adjustment Mode'"></span>
                </button>

                <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-gray-600
                              rounded-lg border border-gray-200 bg-white px-3 py-1.5 hover:bg-gray-50 transition"
                       title="Overwrite the stored defaults for {{ $parsedDate->format('l') }} with the current grid values">
                    <input type="checkbox"
                           x-model="saveAsDefault"
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>Save as Default <span class="font-semibold text-blue-600">({{ $parsedDate->format('l') }})</span></span>
                </label>

                <button type="button"
                        @click="submitForm()"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition disabled:opacity-60">
                    <svg x-show="!saving" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span x-text="saving ? 'Saving…' : 'Save Distribution'"></span>
                </button>
            </div>
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
                    </tr>

                    {{-- ── Board Received Qty row (Adjustment Mode only) ── --}}
                    <tr x-show="adjustMode" style="background:#fffbeb; border-bottom: 2px solid #fcd34d;">
                        <td class="sticky left-0 z-20 px-3 py-2 font-semibold text-[11px] border-r border-amber-300 whitespace-nowrap"
                            style="background:#fffbeb; color:#92400e;">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
                                </svg>
                                Board Received Qty
                            </div>
                        </td>
                        @foreach($lotteries as $l)
                            <td class="px-1 py-1.5 text-center">
                                <input type="number" min="0" step="1"
                                       x-model="boardQty[{{ $l->id }}]"
                                       class="board-qty-cell w-full h-7 px-1 text-center text-xs font-semibold border border-amber-300 rounded bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500 focus:outline-none text-amber-900 placeholder-amber-300"
                                       placeholder="—">
                            </td>
                        @endforeach
                        <td class="px-3 py-1.5 text-center text-xs font-bold text-amber-700">
                            <span x-text="boardGrandTotal().toLocaleString() || '—'"></span>
                        </td>
                    </tr>

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

                <tbody class="divide-y divide-gray-100">
                    @foreach($assistants as $i => $a)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/60' }}"
                            x-bind:class="{
                                'ring-1 ring-inset ring-blue-300 bg-blue-50': hoveredRow === {{ $a->id }} && !isLocked({{ $a->id }}),
                                'ring-1 ring-inset ring-amber-400 bg-amber-50': isLocked({{ $a->id }})
                            }"
                            @mouseenter="hoveredRow = {{ $a->id }}"
                            @mouseleave="hoveredRow = null">

                            <td class="sticky left-0 z-10 px-3 py-1.5 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                                style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}"
                                x-bind:style="isLocked({{ $a->id }}) ? 'background:#fffbeb' : (hoveredRow === {{ $a->id }} ? 'background:#eff6ff' : '')">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-400 text-[11px] shrink-0">{{ $i + 1 }}</span>
                                    <span class="truncate">{{ $a->name }}</span>
                                    {{-- Lock button — only visible in Adjustment Mode --}}
                                    <button type="button"
                                            x-show="adjustMode"
                                            @click.stop="toggleLock({{ $a->id }})"
                                            :title="isLocked({{ $a->id }}) ? 'Unlock row — auto-adjust will include this assistant' : 'Lock row — auto-adjust will skip this assistant'"
                                            :class="isLocked({{ $a->id }}) ? 'text-amber-600 hover:text-amber-700' : 'text-gray-300 hover:text-amber-500'"
                                            class="ml-auto shrink-0 transition-colors">
                                        {{-- Locked icon --}}
                                        <svg x-show="isLocked({{ $a->id }})" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                        </svg>
                                        {{-- Unlocked icon --}}
                                        <svg x-show="!isLocked({{ $a->id }})" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            @foreach($lotteries as $j => $l)
                                <td class="p-0 relative"
                                    x-bind:class="{
                                        'bg-blue-50': hoveredCol === {{ $l->id }} && !isAdjusted({{ $a->id }}, {{ $l->id }}),
                                        'bg-amber-100': isAdjusted({{ $a->id }}, {{ $l->id }})
                                    }">
                                    <input
                                        type="number" min="0" step="1"
                                        class="dist-cell w-full h-8 px-1 text-center text-xs font-medium border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 focus:z-10 relative outline-none"
                                        :value="grid[{{ $a->id }}][{{ $l->id }}] || ''"
                                        :class="{
                                            'text-emerald-700': isDefault({{ $a->id }}, {{ $l->id }}) && !isAdjusted({{ $a->id }}, {{ $l->id }}),
                                            'text-amber-800 font-semibold is-adjusted': isAdjusted({{ $a->id }}, {{ $l->id }})
                                        }"
                                        @focus="hoveredCol = {{ $l->id }}"
                                        @blur="hoveredCol = null"
                                        @input="onCellInput({{ $a->id }}, {{ $l->id }}, $event.target.value)"
                                        data-row="{{ $i }}"
                                        data-col="{{ $j }}"
                                        placeholder="">
                                </td>
                            @endforeach

                            <td class="px-3 py-1.5 text-center font-bold"
                                x-bind:class="rowTotal({{ $a->id }}) > 0 ? 'text-blue-700' : 'text-gray-300'"
                                x-text="rowTotal({{ $a->id }}).toLocaleString()"></td>
                        </tr>
                    @endforeach

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

{{-- Legend --}}
<p class="mt-2 text-xs text-gray-400 print:hidden">
    <span class="inline-block w-3 h-3 rounded-sm bg-emerald-100 border border-emerald-300 mr-1"></span>
    Green values were pre-filled from saved defaults for this day of the week.
    <span class="inline-block w-3 h-3 rounded-sm bg-amber-100 border border-amber-400 mr-1 ml-4"></span>
    Amber values were automatically adjusted to match Board received quantities.
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
input.dist-cell.is-adjusted:focus { background: #fef3c7 !important; box-shadow: inset 0 0 0 2px #d97706 !important; border-radius: 2px; }

/* Board Received Qty row inputs — remove spinners */
input.board-qty-cell::-webkit-outer-spin-button,
input.board-qty-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.board-qty-cell[type=number] { -moz-appearance: textfield; }

/* ── PRINT STYLES ─────────────────────────────────────────────────────── */
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
function distGrid(initialGrid, currentDate, defaultsUrl, saveDefaultsUrl) {
    return {
        // ── State ──────────────────────────────────────────────────────────
        grid:            initialGrid,
        defaultsGrid:    {},
        hoveredRow:      null,
        hoveredCol:      null,
        saveAsDefault:   false,
        defaultsLoading: false,
        defaultsApplied: false,
        defaultsChecked: false,
        dayName:         '',
        saving:          false,

        // ── Adjustment Mode ────────────────────────────────────────────────
        adjustMode:     false,
        boardQty:       {},   // { [lotteryId]: raw input value }
        adjustedCells:  {},   // { [aId]: { [lId]: true } }
        lockedRows:     {},   // { [aId]: true }

        // ── Lifecycle ──────────────────────────────────────────────────────
        init() {
            if (this.grandTotal() === 0) {
                this.fetchDefaults();
            } else {
                fetch(defaultsUrl + '?date=' + encodeURIComponent(currentDate), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => { this.dayName = data.day_name; this.defaultsChecked = true; })
                .catch(() => {});
            }
        },

        // ── Smart Defaults ─────────────────────────────────────────────────
        fetchDefaults() {
            this.defaultsLoading = true;
            this.defaultsApplied = false;
            this.defaultsChecked = false;

            fetch(defaultsUrl + '?date=' + encodeURIComponent(currentDate), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.dayName = data.day_name;
                const defaults = data.defaults || {};
                let applied = false;

                for (const [aId, lotteries] of Object.entries(defaults)) {
                    for (const [lId, qty] of Object.entries(lotteries)) {
                        if (qty > 0) {
                            if (!this.grid[aId]) this.grid[aId] = {};
                            this.grid[aId][lId] = qty;
                            if (!this.defaultsGrid[aId]) this.defaultsGrid[aId] = {};
                            this.defaultsGrid[aId][lId] = qty;
                            applied = true;
                        }
                    }
                }
                this.defaultsApplied = applied;
            })
            .catch(() => {})
            .finally(() => {
                this.defaultsLoading = false;
                this.defaultsChecked = true;
            });
        },

        // ── Cell helpers ───────────────────────────────────────────────────
        isDefault(aId, lId) {
            const defVal = (this.defaultsGrid[aId] ?? {})[lId] ?? 0;
            const curVal = (this.grid[aId] ?? {})[lId] ?? 0;
            return defVal > 0 && defVal === curVal;
        },

        onCellInput(aId, lId, rawValue) {
            const qty = parseInt(rawValue) || 0;
            if (!this.grid[aId]) this.grid[aId] = {};
            this.grid[aId][lId] = qty;
            // Clear adjustment highlight when user manually edits the cell
            if (this.adjustedCells[aId]) delete this.adjustedCells[aId][lId];
        },

        clearGrid() {
            for (const aId of Object.keys(this.grid)) {
                for (const lId of Object.keys(this.grid[aId])) {
                    this.grid[aId][lId] = 0;
                }
            }
            this.defaultsGrid    = {};
            this.defaultsApplied = false;
            this.adjustedCells   = {};
            this.boardQty        = {};
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

        boardGrandTotal() {
            return Object.values(this.boardQty).reduce((s, v) => s + (parseInt(v) || 0), 0);
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

        // ── Keyboard navigation ────────────────────────────────────────────
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
        },

        // ── Adjustment Mode ────────────────────────────────────────────────
        toggleAdjustMode() {
            this.adjustMode = !this.adjustMode;
            if (!this.adjustMode) {
                this.adjustedCells = {};
                this.boardQty      = {};
                this.lockedRows    = {};
            }
        },

        isLocked(aId) {
            return !!this.lockedRows[aId];
        },

        toggleLock(aId) {
            this.lockedRows[aId] = !this.lockedRows[aId];
        },

        isAdjusted(aId, lId) {
            return !!((this.adjustedCells[aId] || {})[lId]);
        },

        autoAdjust() {
            for (const lId of Object.keys(this.boardQty)) {
                // Skip columns where board qty was not entered
                const rawVal = this.boardQty[lId];
                if (rawVal === null || rawVal === undefined || rawVal === '') continue;

                const target  = parseInt(rawVal) || 0;
                const current = this.colTotal(parseInt(lId));
                const diff    = target - current;

                if (diff === 0) continue;

                // Collect unlocked assistants with their current qty for this lottery
                const candidates = Object.keys(this.grid)
                    .filter(aId => !this.lockedRows[aId])
                    .map(aId => ({
                        aId,
                        qty: parseInt((this.grid[aId] || {})[lId]) || 0
                    }));

                if (candidates.length === 0) continue;

                const totalUnlocked = candidates.reduce((s, c) => s + c.qty, 0);

                // Cannot reduce when all unlocked assistants already have 0
                if (diff < 0 && totalUnlocked === 0) continue;

                // Compute per-assistant adjustments using the largest-remainder method
                // so the sum of floor adjustments exactly equals diff.
                let fractionals;
                if (totalUnlocked > 0) {
                    fractionals = candidates.map(c => {
                        const exact = (c.qty / totalUnlocked) * diff;
                        return {
                            aId:   c.aId,
                            qty:   c.qty,
                            floor: Math.trunc(exact),          // truncate toward zero
                            frac:  exact - Math.trunc(exact),  // fractional remainder
                        };
                    });
                } else {
                    // All unlocked have 0 qty — distribute increase evenly
                    fractionals = candidates.map(c => {
                        const exact = diff / candidates.length;
                        return {
                            aId:   c.aId,
                            qty:   c.qty,
                            floor: Math.trunc(exact),
                            frac:  exact - Math.trunc(exact),
                        };
                    });
                }

                // Distribute the integer remainder to candidates with largest fractional parts
                let remainder = diff - fractionals.reduce((s, f) => s + f.floor, 0);

                if (remainder > 0) {
                    // Positive remainder: give +1 to those with highest positive fraction
                    fractionals.sort((a, b) => b.frac - a.frac);
                    for (let i = 0; remainder > 0; i++, remainder--) {
                        fractionals[i % fractionals.length].floor += 1;
                    }
                } else if (remainder < 0) {
                    // Negative remainder: give -1 to those with most negative fraction
                    fractionals.sort((a, b) => a.frac - b.frac);
                    for (let i = 0; remainder < 0; i++, remainder++) {
                        fractionals[i % fractionals.length].floor -= 1;
                    }
                }

                // Apply adjustments (clamp at 0 — never go negative)
                for (const { aId, qty, floor: adj } of fractionals) {
                    if (adj === 0) continue;
                    const newQty = Math.max(0, qty + adj);
                    if (newQty !== qty) {
                        if (!this.grid[aId]) this.grid[aId] = {};
                        this.grid[aId][lId] = newQty;
                        if (!this.adjustedCells[aId]) this.adjustedCells[aId] = {};
                        this.adjustedCells[aId][lId] = true;
                    }
                }
            }
        },
    };
}
</script>
@endpush

</x-layouts.app>
