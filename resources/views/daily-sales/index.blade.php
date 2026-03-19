<x-layouts.app title="Daily Sales Entry">

@php
    $parsedDate     = \Carbon\Carbon::parse($date);
    $assistantNames = $assistants->pluck('name', 'id')->toArray();

    // ── Route Color Map (matches Ticket Distribution screen) ─────────────────
    $routeColorMap = [
        'blue'   => [
            'headerBg'    => 'bg-blue-200',   'headerText'   => 'text-blue-900',
            'headerBgHex' => '#bfdbfe',
            'rowBg'       => 'bg-blue-50',    'rowBgHex'     => '#eff6ff',
            'rowHoverHex' => '#dbeafe',
            'separator'   => 'border-blue-200',
        ],
        'green'  => [
            'headerBg'    => 'bg-green-200',  'headerText'   => 'text-green-900',
            'headerBgHex' => '#bbf7d0',
            'rowBg'       => 'bg-green-50',   'rowBgHex'     => '#f0fdf4',
            'rowHoverHex' => '#dcfce7',
            'separator'   => 'border-green-200',
        ],
        'yellow' => [
            'headerBg'    => 'bg-yellow-200', 'headerText'   => 'text-yellow-900',
            'headerBgHex' => '#fef08a',
            'rowBg'       => 'bg-yellow-50',  'rowBgHex'     => '#fefce8',
            'rowHoverHex' => '#fef9c3',
            'separator'   => 'border-yellow-200',
        ],
        'pink'   => [
            'headerBg'    => 'bg-pink-200',   'headerText'   => 'text-pink-900',
            'headerBgHex' => '#fbcfe8',
            'rowBg'       => 'bg-pink-50',    'rowBgHex'     => '#fdf2f8',
            'rowHoverHex' => '#fce7f3',
            'separator'   => 'border-pink-200',
        ],
    ];
    $defaultRouteColor = [
        'headerBg'    => 'bg-gray-100',   'headerText'   => 'text-gray-500',
        'headerBgHex' => '#f3f4f6',
        'rowBg'       => 'bg-white',      'rowBgHex'     => '#ffffff',
        'rowHoverHex' => '#eff6ff',
        'separator'   => 'border-gray-100',
    ];

    // Group assistants by route; sort named routes alpha, unassigned last.
    // Within each group the controller already ordered by created_at DESC.
    $routeGroups = $assistants
        ->groupBy(fn ($a) => $a->route_id ?? 'unassigned')
        ->map(fn ($group) => [
            'route'      => $group->first()->route,
            'assistants' => $group,
        ])
        ->sortBy(fn ($g) => $g['route']?->name ?? 'ZZZZZ')
        ->values();
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     TOP BAR — date navigation + actions
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">

    <div class="flex items-center gap-2">
        <a href="{{ route('daily-sales.index', ['date' => $parsedDate->copy()->subDay()->toDateString()]) }}"
           class="btn-action rounded-xl border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-800 px-3 py-2 text-sm
                  text-slate-600 dark:text-slate-300
                  hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">‹</a>

        <form id="date-nav-form" method="GET" action="{{ route('daily-sales.index') }}">
            <input type="date" name="date" value="{{ $date }}"
                   onchange="this.form.submit()"
                   class="erp-input text-sm h-9 font-semibold">
        </form>

        <a href="{{ route('daily-sales.index', ['date' => $parsedDate->copy()->addDay()->toDateString()]) }}"
           class="btn-action rounded-xl border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-800 px-3 py-2 text-sm
                  text-slate-600 dark:text-slate-300
                  hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors
                  {{ $parsedDate->isToday() ? 'opacity-40 pointer-events-none' : '' }}">›</a>

        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
            {{ $parsedDate->format('l') }}
        </span>
        @if($parsedDate->isToday())
            <span class="rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                Today
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('daily-sales.analysis') }}"
           class="btn-action inline-flex items-center gap-1.5 rounded-xl
                  border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-800
                  px-4 py-2 text-sm font-medium
                  text-slate-700 dark:text-slate-300
                  hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Analysis
        </a>
        <button onclick="window.print()"
                class="btn-action inline-flex items-center gap-1.5 rounded-xl
                       border border-slate-200 dark:border-slate-700
                       bg-white dark:bg-slate-800
                       px-4 py-2 text-sm font-medium
                       text-slate-700 dark:text-slate-300
                       hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>
</div>

@if($assistants->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700
                bg-white dark:bg-slate-800/40 py-16 text-center">
        <div class="mx-auto h-16 w-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
            <svg class="h-8 w-8 text-slate-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No assistants configured.</p>
        <p class="text-xs text-slate-400 mt-1">
            <a href="{{ route('assistants.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Add assistants →</a>
        </p>
    </div>
@else

{{-- Alpine.js — Sales Grid + Cash Counter Modal ──────────────────────────── --}}
<div x-data="salesGrid({{ json_encode($alpineRows) }}, {{ json_encode($assistantNames) }})"
     @keydown.escape.window="pwModal.open ? cancelPassword() : (addForm.cashOpen ? (addForm.cashOpen = false) : addForm.open ? (addForm.open = false) : (cashModal.open = false))">

    {{-- ── Live Day Summary Tiles ──────────────────────────────────────────── --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-2xl bg-slate-800 dark:bg-slate-700/80 px-4 py-3.5 text-white shadow-sm">
            <p class="text-xs text-slate-400 dark:text-slate-400 mb-1 font-medium">Total Value</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalValue())"></p>
        </div>
        <div class="rounded-2xl bg-emerald-600 dark:bg-emerald-600/80 px-4 py-3.5 text-white shadow-sm">
            <p class="text-xs text-emerald-200 mb-1 font-medium">Total Cash</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalCash())"></p>
        </div>
        <div class="rounded-2xl bg-violet-600 dark:bg-violet-600/80 px-4 py-3.5 text-white shadow-sm">
            <p class="text-xs text-violet-200 mb-1 font-medium">Total Winning</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalWinning())"></p>
        </div>
        <div class="rounded-2xl bg-indigo-600 dark:bg-indigo-600/80 px-4 py-3.5 text-white shadow-sm">
            <p class="text-xs text-indigo-200 mb-1 font-medium">Total C+W</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(totalCW())"></p>
        </div>
        <div class="rounded-2xl px-4 py-3.5 text-white shadow-sm"
             :class="totalBalance() > 0 ? 'bg-red-600 dark:bg-red-600/80' : (totalBalance() < 0 ? 'bg-amber-500 dark:bg-amber-500/80' : 'bg-slate-500 dark:bg-slate-600/80')">
            <p class="text-xs opacity-75 mb-1 font-medium">Credit</p>
            <p class="text-lg font-bold" x-text="'Rs. ' + fmt(Math.abs(totalBalance()))"></p>
        </div>
    </div>

    {{-- ── Form ─────────────────────────────────────────────────────────────── --}}
    <form id="sales-form" method="POST" action="{{ route('daily-sales.store') }}"
          @submit="isDirty = false">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        {{-- Hidden inputs synced from Alpine --}}
        @foreach($assistants as $a)
        @php $aid = $a->id; @endphp
        <input type="hidden" :name="`rows[{{ $aid }}][qty]`"         :value="rows[{{ $aid }}].qty">
        <input type="hidden" :name="`rows[{{ $aid }}][unit_price]`"  :value="rows[{{ $aid }}].unitPrice">
        <input type="hidden" :name="`rows[{{ $aid }}][d5]`"          :value="rows[{{ $aid }}].d5">
        <input type="hidden" :name="`rows[{{ $aid }}][d10]`"         :value="rows[{{ $aid }}].d10">
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

        {{-- Save bar --}}
        <div class="mb-3 flex items-center justify-between print:hidden">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ $parsedDate->format('Y-m-d') }} — {{ $parsedDate->format('l') }}
            </span>
            <div class="flex items-center gap-2">

                {{-- Edit lock badge --}}
                <span class="flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-bold cursor-default select-none"
                      :class="editLocked ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'">
                    <svg x-show="editLocked" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="!editLocked" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    <span x-text="editLocked ? (successCount === 1 ? '1 of 2 verified' : 'Locked') : 'Unlocked'"></span>
                </span>

                <button type="button"
                        @click="requireUnlock(() => openAddForm())"
                        class="btn-action inline-flex items-center gap-2 rounded-xl border border-indigo-300 dark:border-indigo-700
                               bg-white dark:bg-slate-800
                               px-4 py-2 text-sm font-semibold
                               text-indigo-700 dark:text-indigo-300
                               hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Form
                </button>
                <button type="submit"
                        class="btn-action inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700
                               px-5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-900/20 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save All Records
                </button>
            </div>
        </div>

        {{-- ── Search bar ──────────────────────────────────────────────────── --}}
        <div class="mb-2 flex items-center gap-2 print:hidden">
            <div class="relative w-64">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="Search assistant name…"
                       autocomplete="off"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700
                              bg-white dark:bg-slate-800
                              pl-8 pr-7 py-1.5 text-xs
                              text-slate-700 dark:text-slate-300
                              placeholder-slate-400 dark:placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-500/40 focus:border-indigo-300">
                <button x-show="search"
                        @click="search = ''"
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                        title="Clear search">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <span x-show="search"
                  class="text-xs text-slate-400 dark:text-slate-500"
                  x-text="hasAnyMatch() ? '' : 'No results'"></span>
        </div>

        {{-- ── GRID TABLE ───────────────────────────────────────────────────── --}}
        <div class="overflow-auto max-h-[600px] rounded-2xl border border-slate-200 dark:border-slate-700/60
                    bg-white dark:bg-slate-800/60 shadow-sm">
            <table class="min-w-full border-collapse text-xs" id="sales-table">

                {{-- Sticky column headers --}}
                <thead class="sticky top-0 z-20">
                    <tr style="background:#0f172a;">
                        <th class="sticky left-0 z-30 px-3 py-3 text-left text-white font-medium whitespace-nowrap border-x border-slate-600"
                            style="background:#0f172a; min-width:150px;">#&nbsp; Name</th>
                        <th class="px-2 py-3 text-center text-indigo-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:68px;">Amount</th>
                        <th class="px-2 py-3 text-center text-blue-200 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:60px;">Unit<br>Price</th>
                        <th class="px-2 py-3 text-center text-yellow-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:72px;">
                            Value<br><span class="text-slate-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-emerald-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:80px;">
                            Cash<br><span class="text-slate-500 font-normal" style="font-size:10px;">click to count</span>
                        </th>
                        <th class="px-2 py-3 text-center text-violet-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:66px;">NLB<br>Winning</th>
                        <th class="px-2 py-3 text-center text-violet-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:66px;">DLB<br>Winning</th>
                        <th class="px-2 py-3 text-center text-purple-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:62px;">
                            TW<br><span class="text-slate-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-cyan-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:62px;">
                            C+W<br><span class="text-slate-500 font-normal" style="font-size:10px;">auto</span>
                        </th>
                        <th class="px-2 py-3 text-center text-red-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:90px;">
                            Status<br><span class="text-slate-500 font-normal" style="font-size:10px;">Balance</span>
                        </th>
                        <th class="px-2 py-3 text-center text-slate-300 font-medium whitespace-nowrap border-x border-slate-600" style="min-width:130px;">Remarks</th>
                    </tr>

                    {{-- Totals row --}}
                    <tr style="background:#1e293b;" class="border-b-2 border-slate-600">
                        <td class="sticky left-0 z-30 px-3 py-2 text-slate-300 font-semibold text-[11px] border-x border-slate-600"
                            style="background:#1e293b;">Totals ↓</td>
                        <td class="px-2 py-2 text-center text-slate-200 font-bold border-x border-slate-600" x-text="fmtInt(totalQty())"></td>
                        <td class="px-2 py-2 text-center text-slate-500 border-x border-slate-600">—</td>
                        <td class="px-2 py-2 text-center text-yellow-300 font-bold border-x border-slate-600"  x-text="fmt(totalValue())"></td>
                        <td class="px-2 py-2 text-center text-emerald-300 font-bold border-x border-slate-600" x-text="fmt(totalCash())"></td>
                        <td class="px-2 py-2 text-center text-violet-300 font-bold border-x border-slate-600"  x-text="fmt(totalNlb())"></td>
                        <td class="px-2 py-2 text-center text-violet-300 font-bold border-x border-slate-600"  x-text="fmt(totalDlb())"></td>
                        <td class="px-2 py-2 text-center text-purple-300 font-bold border-x border-slate-600"  x-text="fmt(totalWinning())"></td>
                        <td class="px-2 py-2 text-center text-cyan-300 font-bold border-x border-slate-600"    x-text="fmt(totalCW())"></td>
                        <td class="px-2 py-2 text-center text-red-300 font-bold border-x border-slate-600"     x-text="fmt(Math.abs(totalBalance()))"></td>
                        <td class="border-x border-slate-600"></td>
                    </tr>
                </thead>

                <tbody>
                    @php $rowIndex = 0; @endphp
                    @foreach($routeGroups as $routeGroup)
                    @php
                        $route       = $routeGroup['route'];
                        $colors      = $routeColorMap[$route?->color_code ?? ''] ?? $defaultRouteColor;
                    @endphp

                    {{-- ── Route Group Header ──────────────────────────────── --}}
                    <tr class="border-t-2 border-slate-300 dark:border-slate-600"
                        x-show="groupHasMatch({{ json_encode($routeGroup['assistants']->pluck('id')->values()->toArray()) }})">
                        <td class="sticky left-0 z-10 px-3 py-1.5 font-bold text-[11px] uppercase tracking-widest
                                   {{ $colors['headerText'] }} border-x border-gray-300 dark:border-slate-600"
                            style="background: {{ $colors['headerBgHex'] }};">
                            {{ $route?->name ?? 'Unassigned' }}
                            <span class="ml-1.5 font-normal opacity-60 normal-case tracking-normal">
                                {{ $routeGroup['assistants']->count() }}
                            </span>
                        </td>
                        <td colspan="10" class="{{ $colors['headerBg'] }} border-x border-gray-300 dark:border-slate-600"></td>
                    </tr>

                    {{-- ── Assistant Rows ───────────────────────────────────── --}}
                    @foreach($routeGroup['assistants'] as $a)
                    @php
                        $aid         = $a->id;
                        $ri          = $rowIndex++;
                        $rowBgHex    = $colors['rowBgHex'];
                        $rowHoverHex = $colors['rowHoverHex'];
                    @endphp
                    <tr class="{{ $colors['rowBg'] }} border-b {{ $colors['separator'] }} dark:border-slate-700/40"
                        x-show="matchesSearch({{ $aid }})"
                        :class="activeRow === {{ $aid }} ? 'ring-1 ring-inset ring-indigo-300 dark:ring-indigo-500/40 !bg-indigo-50 dark:!bg-indigo-900/20' : ''"
                        @mouseenter="activeRow = {{ $aid }}"
                        @mouseleave="activeRow = null">

                        {{-- Sticky name cell --}}
                        <td class="sticky left-0 z-10 px-3 py-1.5 font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap border-x border-gray-200 dark:border-slate-700/40"
                            style="background: {{ $rowBgHex }}"
                            :style="activeRow === {{ $aid }}
                                ? (document.documentElement.classList.contains('dark') ? 'background:rgba(99,102,241,0.15)' : 'background:{{ $rowHoverHex }}')
                                : 'background:{{ $rowBgHex }}'">
                            <span class="mr-1 text-slate-400 dark:text-slate-500 text-[11px]">{{ $ri + 1 }}</span>
                            {{ $a->name }}
                        </td>

                        {{-- Amount --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <input type="number" min="0" step="1"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none text-slate-800 dark:text-slate-200"
                                   :value="rows[{{ $aid }}].qty || ''"
                                   @input="editCell({{ $aid }}, 'qty', $event.target.value, 'int')">
                        </td>

                        {{-- Unit Price --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none text-slate-800 dark:text-slate-200"
                                   :value="rows[{{ $aid }}].unitPrice"
                                   @input="editCell({{ $aid }}, 'unitPrice', $event.target.value, 'float')">
                        </td>

                        {{-- Value (auto) --}}
                        <td class="px-2 py-1.5 text-center font-semibold text-yellow-700 dark:text-yellow-400 bg-yellow-50/50 dark:bg-yellow-900/10 border-x border-gray-200 dark:border-slate-700/40"
                            x-text="fmt(value({{ $aid }}))"></td>

                        {{-- Cash (opens modal) --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <button type="button"
                                    class="btn-action w-full h-8 px-2 text-center text-xs font-semibold
                                           text-emerald-700 dark:text-emerald-400
                                           bg-emerald-50 dark:bg-emerald-900/20
                                           hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors cursor-pointer"
                                    @click="requireUnlock(() => openCashCounter({{ $aid }}))"
                                    x-text="hasCash({{ $aid }}) ? fmt(cash({{ $aid }})) : '+ Count'">
                            </button>
                        </td>

                        {{-- NLB Winning --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none text-slate-800 dark:text-slate-200"
                                   :value="rows[{{ $aid }}].nlbWinning || ''"
                                   @input="editCell({{ $aid }}, 'nlbWinning', $event.target.value, 'float')">
                        </td>

                        {{-- DLB Winning --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <input type="number" min="0" step="0.01"
                                   class="ds-cell w-full h-8 px-1 text-center text-xs border-0 bg-transparent outline-none text-slate-800 dark:text-slate-200"
                                   :value="rows[{{ $aid }}].dlbWinning || ''"
                                   @input="editCell({{ $aid }}, 'dlbWinning', $event.target.value, 'float')">
                        </td>

                        {{-- TW (auto) --}}
                        <td class="px-2 py-1.5 text-center text-purple-700 dark:text-purple-400 font-medium bg-purple-50/30 dark:bg-purple-900/10 border-x border-gray-200 dark:border-slate-700/40"
                            x-text="fmt(tw({{ $aid }}))"></td>

                        {{-- C+W (auto) --}}
                        <td class="px-2 py-1.5 text-center text-cyan-700 dark:text-cyan-400 font-semibold bg-cyan-50/30 dark:bg-cyan-900/10 border-x border-gray-200 dark:border-slate-700/40"
                            x-text="fmt(cw({{ $aid }}))"></td>

                        {{-- Status --}}
                        <td class="px-1 py-1.5 text-center border-x border-gray-200 dark:border-slate-700/40">
                            <template x-if="balance({{ $aid }}) > 0">
                                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 px-1.5 py-0.5 text-center">
                                    <p class="text-[9px] font-bold text-red-500 dark:text-red-400 uppercase">Credit</p>
                                    <p class="text-xs font-bold text-red-700 dark:text-red-300" x-text="fmt(balance({{ $aid }}))"></p>
                                </div>
                            </template>
                            <template x-if="balance({{ $aid }}) < 0">
                                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 px-1.5 py-0.5 text-center">
                                    <p class="text-[9px] font-bold text-amber-500 dark:text-amber-400 uppercase">Excess</p>
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-300" x-text="fmt(Math.abs(balance({{ $aid }})))"></p>
                                </div>
                            </template>
                            <template x-if="balance({{ $aid }}) === 0 && value({{ $aid }}) > 0">
                                <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold
                                             bg-emerald-100 dark:bg-emerald-900/25 text-emerald-700 dark:text-emerald-400">
                                    Settled
                                </span>
                            </template>
                        </td>

                        {{-- Remarks --}}
                        <td class="p-0 border-x border-gray-200 dark:border-slate-700/40">
                            <input type="text"
                                   class="ds-cell w-full h-8 px-2 text-xs border-0 bg-transparent outline-none text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600"
                                   :value="rows[{{ $aid }}].remarks"
                                   @input="editCell({{ $aid }}, 'remarks', $event.target.value, 'text')"
                                   placeholder="Notes…">
                        </td>
                    </tr>
                    @endforeach
                    @endforeach

                    {{-- No search match --}}
                    <tr x-show="search && !hasAnyMatch()">
                        <td colspan="11"
                            class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">
                            <svg class="mx-auto mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                            </svg>
                            No assistant found matching "<span x-text="search" class="font-semibold text-slate-500 dark:text-slate-400"></span>"
                        </td>
                    </tr>

                    {{-- Bottom totals row --}}
                    <tr class="border-t-2 border-slate-200 dark:border-slate-600 font-bold bg-slate-50 dark:bg-slate-700/40">
                        <td class="sticky left-0 z-10 px-3 py-2.5 text-slate-700 dark:text-slate-300 border-x border-gray-200 dark:border-slate-600 ds-sticky-footer"
                            style="background:#f8fafc;">Totals ↑</td>
                        <td class="px-2 py-2.5 text-center text-slate-900 dark:text-white border-x border-gray-200 dark:border-slate-600" x-text="fmtInt(totalQty())"></td>
                        <td class="px-2 py-2.5 text-center text-slate-400 dark:text-slate-500 border-x border-gray-200 dark:border-slate-600">—</td>
                        <td class="px-2 py-2.5 text-center text-yellow-700 dark:text-yellow-400 font-bold border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalValue())"></td>
                        <td class="px-2 py-2.5 text-center text-emerald-700 dark:text-emerald-400 font-bold border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalCash())"></td>
                        <td class="px-2 py-2.5 text-center text-violet-700 dark:text-violet-400 border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalNlb())"></td>
                        <td class="px-2 py-2.5 text-center text-violet-700 dark:text-violet-400 border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalDlb())"></td>
                        <td class="px-2 py-2.5 text-center text-purple-700 dark:text-purple-400 font-bold border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalWinning())"></td>
                        <td class="px-2 py-2.5 text-center text-cyan-700 dark:text-cyan-400 font-bold border-x border-gray-200 dark:border-slate-600" x-text="fmt(totalCW())"></td>
                        <td class="px-2 py-2.5 text-center font-bold border-x border-gray-200 dark:border-slate-600"
                            :class="totalBalance() > 0 ? 'text-red-700 dark:text-red-400' : (totalBalance() < 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400')"
                            x-text="fmt(Math.abs(totalBalance()))"></td>
                        <td class="border-x border-gray-200 dark:border-slate-600"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

    {{-- ── Password Modal ──────────────────────────────────────────────────── --}}
    <div x-show="pwModal.open"
         class="fixed inset-0 z-[60] flex items-center justify-center print:hidden"
         style="display:none;">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelPassword()"></div>
        <div class="relative z-10 w-full max-w-sm mx-4 rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6"
             x-effect="if (pwModal.open) $nextTick(() => { const i = $el.querySelector('input[type=password]'); if (i) i.focus(); })">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 shrink-0">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Table is Locked</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400"
                       x-text="successCount === 0 ? 'Enter password to make your 1st edit.' : 'Enter password to confirm your 2nd edit.'"></p>
                </div>
            </div>
            <input type="password"
                   x-model="pwModal.input"
                   @keydown.enter="submitPassword()"
                   @keydown.escape.prevent="cancelPassword()"
                   placeholder="Enter password"
                   class="w-full rounded-lg border border-gray-300 dark:border-slate-600 px-3 py-2 text-sm text-gray-800 dark:text-white dark:bg-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-500/30 mb-1">
            <p x-show="pwModal.error" x-text="pwModal.error" class="text-xs text-red-600 dark:text-red-400 min-h-[1rem] mb-1"></p>
            <div class="flex gap-2 mt-3">
                <button type="button" @click="cancelPassword()"
                        class="flex-1 rounded-lg border border-gray-200 dark:border-slate-600 px-4 py-2 text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                    Cancel
                </button>
                <button type="button" @click="submitPassword()"
                        class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                    Unlock
                </button>
            </div>
        </div>
    </div>

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
                       x-text="cashModal.assistantId ? (names[cashModal.assistantId] ?? '') : ''"></p>
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
                @foreach([5000 => ['bg-purple-100 dark:bg-purple-900/30','text-purple-800 dark:text-purple-300'], 1000 => ['bg-blue-100 dark:bg-blue-900/30','text-blue-800 dark:text-blue-300'], 500 => ['bg-emerald-100 dark:bg-emerald-900/30','text-emerald-800 dark:text-emerald-300'], 100 => ['bg-yellow-100 dark:bg-yellow-900/30','text-yellow-800 dark:text-yellow-300'], 50 => ['bg-orange-100 dark:bg-orange-900/30','text-orange-800 dark:text-orange-300'], 20 => ['bg-slate-100 dark:bg-slate-700','text-slate-700 dark:text-slate-300'], 10 => ['bg-red-100 dark:bg-red-900/30','text-red-800 dark:text-red-300'], 5 => ['bg-pink-100 dark:bg-pink-900/30','text-pink-800 dark:text-pink-300']] as $denom => $cls)
                <div class="flex items-center gap-3">
                    <span class="w-20 flex-shrink-0 rounded-full {{ $cls[0] }} {{ $cls[1] }} px-3 py-1 text-center text-xs font-bold">
                        Rs. {{ number_format($denom) }}
                    </span>
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                    <input type="number" min="0" step="1" placeholder="0"
                           class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50
                                  text-slate-900 dark:text-white
                                  px-3 py-1.5 text-sm text-center font-medium
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50"
                           :value="cashModal.denoms[{{ $denom }}]"
                           @input="cashModal.denoms[{{ $denom }}] = parseInt($event.target.value) || 0"
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
                            class="btn-action flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                                   bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                                   text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </button>
                    <button type="button" @click="resetCash()"
                            class="btn-action flex-1 rounded-xl border border-red-300 dark:border-red-600
                                   bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                                   text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Reset
                    </button>
                    <button type="button" @click="applyCash()"
                            class="btn-action flex-1 rounded-xl bg-emerald-600 hover:bg-emerald-700
                                   py-2.5 text-sm font-semibold text-white transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         ADD FORM MODAL — single-entry popup with searchable assistant picker
         and embedded cash counter (two-panel, no nested overlay)
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="addForm.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden"
         @click.self="addForm.open = false"
         style="display:none;">

        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl overflow-hidden ring-1 ring-black/5 dark:ring-white/5"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal header --}}
            <div class="flex items-center justify-between bg-slate-900 dark:bg-slate-950 px-5 py-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium"
                       x-text="addForm.cashOpen ? 'Cash Counter' : 'Add Single Entry'"></p>
                    <p class="font-bold text-white text-sm mt-0.5"
                       x-text="addForm.cashOpen
                           ? (names[addForm.assistantId] ?? 'Count Cash')
                           : '{{ $parsedDate->format('d M Y') }}'"></p>
                </div>
                <button type="button" @click="addForm.open = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- ── MAIN FORM PANEL ─────────────────────────────────────────── --}}
            <div x-show="!addForm.cashOpen" class="px-5 py-4 space-y-3.5">

                {{-- Sales Assistant — searchable dropdown --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                        Sales Assistant <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               x-model="addForm.search"
                               @focus="addForm.dropOpen = true"
                               @input="addForm.dropOpen = true; addForm.assistantId = ''"
                               placeholder="Type to search assistant…"
                               autocomplete="off"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50
                                      placeholder-slate-400 dark:placeholder-slate-500">
                        <div x-show="addForm.dropOpen && filteredAssistants().length > 0"
                             @click.outside="addForm.dropOpen = false"
                             class="absolute z-10 mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700
                                    bg-white dark:bg-slate-800 shadow-lg overflow-hidden max-h-44 overflow-y-auto"
                             style="display:none;">
                            <template x-for="pair in filteredAssistants()" :key="pair[0]">
                                <button type="button"
                                        @mousedown.prevent="selectAssistant(pair[0], pair[1])"
                                        class="w-full px-3 py-2 text-left text-sm text-slate-700 dark:text-slate-300
                                               hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        x-text="pair[1]">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Amount & Unit Price --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Amount (Units)</label>
                        <input type="number" min="0" step="1"
                               x-model.number="addForm.qty"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-3 py-2 text-sm text-center font-medium
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Unit Price</label>
                        <input type="number" min="0" step="0.01"
                               x-model.number="addForm.unitPrice"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-3 py-2 text-sm text-center font-medium
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50">
                    </div>
                </div>

                {{-- Value (auto) + Cash button --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Value <span class="font-normal text-slate-400">(auto)</span>
                        </label>
                        <div class="rounded-xl border border-yellow-200 dark:border-yellow-800/40
                                    bg-yellow-50/60 dark:bg-yellow-900/10
                                    px-3 py-2 text-sm font-semibold text-center
                                    text-yellow-700 dark:text-yellow-400"
                             x-text="'Rs. ' + fmt(addFormValue())"></div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Cash <span class="font-normal text-slate-400">(click to count)</span>
                        </label>
                        <button type="button"
                                @click="addForm.cashOpen = true"
                                class="btn-action w-full rounded-xl border border-emerald-200 dark:border-emerald-700
                                       bg-emerald-50 dark:bg-emerald-900/20
                                       px-3 py-2 text-sm font-semibold
                                       text-emerald-700 dark:text-emerald-400
                                       hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors"
                                x-text="addFormCashTotal() > 0 ? 'Rs. ' + fmt(addFormCashTotal()) : '+ Count Cash'">
                        </button>
                    </div>
                </div>

                {{-- NLB & DLB Winning --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">NLB Winning</label>
                        <input type="number" min="0" step="0.01"
                               x-model.number="addForm.nlbWinning"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-3 py-2 text-sm text-center font-medium
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">DLB Winning</label>
                        <input type="number" min="0" step="0.01"
                               x-model.number="addForm.dlbWinning"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                      px-3 py-2 text-sm text-center font-medium
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50">
                    </div>
                </div>

                {{-- Remarks --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Remarks</label>
                    <input type="text"
                           x-model="addForm.remarks"
                           placeholder="Optional notes…"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50 text-slate-900 dark:text-white
                                  px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50
                                  placeholder-slate-400 dark:placeholder-slate-500">
                </div>

                {{-- Live summary strip --}}
                <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 dark:bg-slate-700/30 px-3 py-2.5">
                    <div class="text-center">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mb-0.5">TW</p>
                        <p class="text-sm font-bold text-purple-700 dark:text-purple-400" x-text="fmt(addFormTW())"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mb-0.5">C+W</p>
                        <p class="text-sm font-bold text-cyan-700 dark:text-cyan-400" x-text="fmt(addFormCW())"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mb-0.5">Balance</p>
                        <p class="text-sm font-bold"
                           :class="addFormBalance() > 0
                               ? 'text-red-600 dark:text-red-400'
                               : addFormBalance() < 0
                                   ? 'text-amber-600 dark:text-amber-400'
                                   : 'text-emerald-600 dark:text-emerald-400'"
                           x-text="fmt(Math.abs(addFormBalance()))"></p>
                    </div>
                </div>
            </div>

            {{-- Main form footer --}}
            <div x-show="!addForm.cashOpen"
                 class="border-t border-slate-100 dark:border-slate-700/60
                        bg-slate-50 dark:bg-slate-900/40 px-5 py-4 flex gap-3">
                <button type="button"
                        @click="addForm.open = false"
                        class="btn-action flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                               bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                               text-slate-700 dark:text-slate-300
                               hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="button"
                        @click="submitAddForm()"
                        :disabled="!addForm.assistantId"
                        class="btn-action flex-[2] rounded-xl bg-indigo-600 hover:bg-indigo-700
                               py-2.5 text-sm font-semibold text-white transition-colors
                               disabled:opacity-40 disabled:cursor-not-allowed">
                    Add to Table
                </button>
            </div>

            {{-- ── CASH COUNTER PANEL (within the add form modal) ─────────── --}}
            <div x-show="addForm.cashOpen" class="px-5 py-4 space-y-2.5">
                @foreach([5000 => ['bg-purple-100 dark:bg-purple-900/30','text-purple-800 dark:text-purple-300'], 1000 => ['bg-blue-100 dark:bg-blue-900/30','text-blue-800 dark:text-blue-300'], 500 => ['bg-emerald-100 dark:bg-emerald-900/30','text-emerald-800 dark:text-emerald-300'], 100 => ['bg-yellow-100 dark:bg-yellow-900/30','text-yellow-800 dark:text-yellow-300'], 50 => ['bg-orange-100 dark:bg-orange-900/30','text-orange-800 dark:text-orange-300'], 20 => ['bg-slate-100 dark:bg-slate-700','text-slate-700 dark:text-slate-300'], 10 => ['bg-red-100 dark:bg-red-900/30','text-red-800 dark:text-red-300'], 5 => ['bg-pink-100 dark:bg-pink-900/30','text-pink-800 dark:text-pink-300']] as $denom => $cls)
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
                           :value="addForm.denoms[{{ $denom }}]"
                           @input="addForm.denoms[{{ $denom }}] = parseInt($event.target.value) || 0"
                           @keydown.enter.prevent="addForm.cashOpen = false">
                    <span class="w-24 flex-shrink-0 text-right text-sm font-semibold text-slate-700 dark:text-slate-300"
                          x-text="'Rs. ' + ((addForm.denoms[{{ $denom }}]||0)*{{ $denom }}).toLocaleString()"></span>
                </div>
                @endforeach
            </div>

            {{-- Cash counter footer --}}
            <div x-show="addForm.cashOpen"
                 class="border-t border-slate-100 dark:border-slate-700/60
                        bg-slate-50 dark:bg-slate-900/40 px-5 py-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Cash</span>
                    <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-400"
                          x-text="'Rs. ' + fmt(addFormCashTotal())"></span>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                            @click="addForm.cashOpen = false"
                            class="btn-action flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                                   bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                                   text-slate-700 dark:text-slate-300
                                   hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        ← Back
                    </button>
                    <button type="button"
                            @click="addForm.denoms = { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 }"
                            class="btn-action flex-1 rounded-xl border border-red-300 dark:border-red-600
                                   bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                                   text-red-600 dark:text-red-400
                                   hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Reset
                    </button>
                    <button type="button"
                            @click="addForm.cashOpen = false"
                            class="btn-action flex-1 rounded-xl bg-emerald-600 hover:bg-emerald-700
                                   py-2.5 text-sm font-semibold text-white transition-colors">
                        Apply
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>{{-- /x-data --}}

{{-- ══════════════════════════════════════════════════════════════════════
     DATE NAVIGATION BAR
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mt-5 flex gap-1.5 overflow-x-auto pb-1 print:hidden">
    @foreach($navDates as $d)
    @php $np = \Carbon\Carbon::parse($d); @endphp
    <a href="{{ route('daily-sales.index', ['date' => $d]) }}"
       class="btn-action flex-shrink-0 rounded-xl px-4 py-2 text-xs font-medium transition-all text-center
              {{ $d === $date
                 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-900/20'
                 : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
        <span class="block font-bold">{{ $np->format('d') }}</span>
        <span class="block opacity-75">{{ $np->format('M') }}</span>
        <span class="block" style="font-size:10px;">{{ $np->format('D') }}</span>
    </a>
    @endforeach
</div>

@endif

@push('head')
<style>
/* ── Sticky column right-edge shadow ────────────────────────── */
#sales-table th.sticky,
#sales-table td.sticky {
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.07);
}
.dark #sales-table th.sticky,
.dark #sales-table td.sticky {
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.30);
}
/* Dark-mode solid background for the bottom totals sticky cell */
.dark #sales-table .ds-sticky-footer {
    background: #1e293b !important;
}
input.ds-cell::-webkit-outer-spin-button,
input.ds-cell::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input.ds-cell[type=number] { -moz-appearance: textfield; }
input.ds-cell:focus {
    background: rgba(99,102,241,0.06) !important;
    box-shadow: inset 0 0 0 2px #6366f1;
    border-radius: 3px;
}
.dark input.ds-cell:focus {
    background: rgba(99,102,241,0.15) !important;
    box-shadow: inset 0 0 0 2px #818cf8;
}
@media print {
    @page { margin: 10mm; size: A4 landscape; }
    body, html { margin: 0 !important; padding: 0 !important; }

    /* Hide screen-only UI */
    .print\:hidden { display: none !important; }
    aside, header { display: none !important; }

    /* ── B&W table: strip all backgrounds, force solid black cell borders ── */
    #sales-table {
        border-collapse: collapse !important;
    }
    #sales-table th,
    #sales-table td {
        border: 1px solid #000 !important;
        background: #fff !important;
        color: #000 !important;
        box-shadow: none !important;
        animation: none !important;
    }
    /* tr-level border-color (route-group header has border-t-2) → black */
    #sales-table tr {
        border-color: #000 !important;
    }
    /* Inputs: transparent fill so the cell white shows through; no own border */
    #sales-table input {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #000 !important;
    }
    /* Cash counter button: strip emerald styling, show value as plain text */
    #sales-table button {
        background: transparent !important;
        border: none !important;
        color: #000 !important;
    }
}
</style>
<script>
function salesGrid(initialRows, names) {
    return {
        rows: initialRows,
        names: names,
        activeRow: null,
        isDirty: false,
        pendingUrl: null,
        _formId: 'sales-form',

        // ── Edit Lock ──────────────────────────────────────────────────────
        editLocked:   true,
        successCount: 0,
        pwModal:      { open: false, pendingCallback: null, input: '', error: '' },
        cashModal: {
            open: false,
            assistantId: null,
            denoms: { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
        },
        addForm: {
            open:        false,
            dropOpen:    false,
            cashOpen:    false,
            assistantId: '',
            search:      '',
            qty:         0,
            unitPrice:   40,
            nlbWinning:  0,
            dlbWinning:  0,
            remarks:     '',
            denoms: { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
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

            // 3. App-level: intercept the date-picker form (onchange="this.form.submit()")
            this._formGuard = (e) => {
                if (!this.isDirty) return;
                e.preventDefault();
                const f = e.target;
                const params = new URLSearchParams(new FormData(f)).toString();
                this._dlpPrompt(f.action + (params ? '?' + params : ''));
            };
            const dateForm = document.getElementById('date-nav-form');
            if (dateForm) dateForm.addEventListener('submit', this._formGuard);
        },
        destroy() {
            window.removeEventListener('beforeunload', this._unloadHandler);
            document.removeEventListener('click', this._clickGuard);
            const dateForm = document.getElementById('date-nav-form');
            if (dateForm) dateForm.removeEventListener('submit', this._formGuard);
        },

        // ── SweetAlert2 DLP prompt ────────────────────────────────────────────
        _dlpPrompt(destUrl) {
            this.pendingUrl = destUrl;
            const dark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Unsaved Changes',
                html: 'Your table has unsaved entries.<br><small style="color:#94a3b8">Choose how to proceed:</small>',
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

        // ── Search / filter ───────────────────────────────────────────────────
        search: '',
        matchesSearch(id) {
            return !this.search || (this.names[id] || '').toLowerCase().includes(this.search.toLowerCase());
        },
        groupHasMatch(ids) {
            return !this.search || ids.some(id => (this.names[id] || '').toLowerCase().includes(this.search.toLowerCase()));
        },
        hasAnyMatch() {
            return !this.search || Object.values(this.names).some(n => n.toLowerCase().includes(this.search.toLowerCase()));
        },

        // ── Row computed ──────────────────────────────────────────────────────
        value(id)   { return (this.rows[id].qty||0) * (this.rows[id].unitPrice||0); },
        cash(id)    {
            const r = this.rows[id];
            return (r.d5||0)*5+(r.d10||0)*10+(r.d20||0)*20+(r.d50||0)*50+(r.d100||0)*100+(r.d500||0)*500+(r.d1000||0)*1000+(r.d5000||0)*5000;
        },
        hasCash(id) { const r=this.rows[id]; return (r.d5||0)+(r.d10||0)+(r.d20||0)+(r.d50||0)+(r.d100||0)+(r.d500||0)+(r.d1000||0)+(r.d5000||0)>0; },
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
            this.cashModal.denoms = { 5:r.d5||0, 10:r.d10||0, 20:r.d20||0, 50:r.d50||0, 100:r.d100||0, 500:r.d500||0, 1000:r.d1000||0, 5000:r.d5000||0 };
            this.cashModal.assistantId = id;
            this.cashModal.open = true;
        },
        cashModalTotal() {
            const d = this.cashModal.denoms;
            return (d[5]||0)*5+(d[10]||0)*10+(d[20]||0)*20+(d[50]||0)*50+(d[100]||0)*100+(d[500]||0)*500+(d[1000]||0)*1000+(d[5000]||0)*5000;
        },
        applyCash() {
            const id = this.cashModal.assistantId;
            const d  = this.cashModal.denoms;
            Object.assign(this.rows[id], { d5:d[5]||0, d10:d[10]||0, d20:d[20]||0, d50:d[50]||0, d100:d[100]||0, d500:d[500]||0, d1000:d[1000]||0, d5000:d[5000]||0 });
            this.isDirty = true;
            this.cashModal.open = false;
        },
        resetCash() {
            this.cashModal.denoms = { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 };
        },

        // ── Add Form ──────────────────────────────────────────────────────────
        openAddForm() {
            this.addForm = {
                open:        true,
                dropOpen:    false,
                cashOpen:    false,
                assistantId: '',
                search:      '',
                qty:         0,
                unitPrice:   40,
                nlbWinning:  0,
                dlbWinning:  0,
                remarks:     '',
                denoms: { 5:0, 10:0, 20:0, 50:0, 100:0, 500:0, 1000:0, 5000:0 },
            };
        },
        filteredAssistants() {
            const q = (this.addForm.search || '').toLowerCase();
            return Object.entries(this.names).filter(([, name]) =>
                !q || name.toLowerCase().includes(q)
            );
        },
        selectAssistant(id, name) {
            this.addForm.assistantId = id;
            this.addForm.search      = name;
            this.addForm.dropOpen    = false;
            // Pre-fill from the existing row so edits show current state
            const r = this.rows[id];
            if (r) {
                this.addForm.qty        = r.qty        || 0;
                this.addForm.unitPrice  = r.unitPrice  || 40;
                this.addForm.nlbWinning = r.nlbWinning || 0;
                this.addForm.dlbWinning = r.dlbWinning || 0;
                this.addForm.remarks    = r.remarks    || '';
                this.addForm.denoms = {
                    5:    r.d5    || 0, 10:   r.d10   || 0,
                    20:   r.d20   || 0, 50:   r.d50   || 0,
                    100:  r.d100  || 0, 500:  r.d500  || 0,
                    1000: r.d1000 || 0, 5000: r.d5000 || 0,
                };
            }
        },
        addFormCashTotal() {
            const d = this.addForm.denoms;
            return (d[5]||0)*5+(d[10]||0)*10+(d[20]||0)*20+(d[50]||0)*50
                 + (d[100]||0)*100+(d[500]||0)*500+(d[1000]||0)*1000+(d[5000]||0)*5000;
        },
        addFormValue()   { return (this.addForm.qty||0) * (this.addForm.unitPrice||0); },
        addFormTW()      { return (this.addForm.nlbWinning||0) + (this.addForm.dlbWinning||0); },
        addFormCW()      { return this.addFormCashTotal() + this.addFormTW(); },
        addFormBalance() { return this.addFormValue() - this.addFormCW(); },
        submitAddForm() {
            if (!this.addForm.assistantId || !this.rows[this.addForm.assistantId]) return;
            const id = this.addForm.assistantId;
            const d  = this.addForm.denoms;
            Object.assign(this.rows[id], {
                qty:        this.addForm.qty,
                unitPrice:  this.addForm.unitPrice,
                d5:    d[5]   ||0, d10:   d[10]  ||0,
                d20:   d[20]  ||0, d50:   d[50]  ||0,
                d100:  d[100] ||0, d500:  d[500] ||0,
                d1000: d[1000]||0, d5000: d[5000]||0,
                nlbWinning: this.addForm.nlbWinning,
                dlbWinning: this.addForm.dlbWinning,
                remarks:    this.addForm.remarks,
            });
            this.isDirty = true;
            this.addForm.open = false;
        },

        // ── Edit Lock methods ──────────────────────────────────────────────
        editCell(id, field, rawValue, type) {
            let v;
            if (type === 'int')   v = parseInt(rawValue)   || 0;
            else if (type === 'float') v = parseFloat(rawValue) || 0;
            else v = rawValue;
            this.requireUnlock(() => { this.rows[id][field] = v; this.isDirty = true; });
        },
        requireUnlock(cb) {
            if (!this.editLocked) { cb(); return; }
            this.pwModal.input = '';
            this.pwModal.error = '';
            this.pwModal.pendingCallback = cb;
            this.pwModal.open = true;
        },
        submitPassword() {
            const EDIT_PASSWORD = '{{ env("TABLE_EDIT_PASSWORD", "admin123") }}';
            if (this.pwModal.input === EDIT_PASSWORD) {
                this.successCount++;
                if (this.successCount >= 2) this.editLocked = false;
                const cb = this.pwModal.pendingCallback;
                this.pwModal.open = false;
                this.pwModal.input = '';
                this.pwModal.error = '';
                this.pwModal.pendingCallback = null;
                if (cb) cb();
            } else {
                this.pwModal.error = 'Incorrect password. Please try again.';
                this.pwModal.input = '';
            }
        },
        cancelPassword() {
            this.pwModal.open = false;
            this.pwModal.input = '';
            this.pwModal.error = '';
            this.pwModal.pendingCallback = null;
        },

        // ── Formatters ────────────────────────────────────────────────────────
        fmt(n)    { return Number(n||0).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0}); },
        fmtInt(n) { return Number(n||0).toLocaleString(); },
    };
}
</script>
@endpush

</x-layouts.app>
