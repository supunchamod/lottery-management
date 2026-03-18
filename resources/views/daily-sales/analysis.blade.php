<x-layouts.app title="Sales Analysis">

{{-- ── Alpine component — defined before Alpine's deferred init ────────── --}}
@push('head')
<script>
function analysisControls() {
    return {
        /* ── state ── */
        period:       '{{ $period }}',
        startDate:    '{{ $startDate }}',
        endDate:      '{{ $endDate }}',
        selected:     {!! json_encode(array_map('intval', $assistantIds), JSON_HEX_TAG) !!},
        allIds:       {!! json_encode(array_map('intval', $allIds),       JSON_HEX_TAG) !!},
        allAssistants:{!! json_encode(
                            $assistants->map(fn($a) => ['id' => (int)$a->id, 'name' => $a->name])
                                       ->values()->toArray(),
                            JSON_HEX_TAG | JSON_UNESCAPED_UNICODE
                        ) !!},
        dropOpen: false,
        search:   '',

        /* ── computed ── */
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return q ? this.allAssistants.filter(a => a.name.toLowerCase().includes(q))
                     : this.allAssistants;
        },
        get allFilteredSelected() {
            const f = this.filtered;
            return f.length > 0 && f.every(a => this.selected.includes(a.id));
        },
        get someFilteredSelected() {
            return !this.allFilteredSelected && this.filtered.some(a => this.selected.includes(a.id));
        },
        get label() {
            if (this.selected.length === 0)                    return 'Select Assistants\u2026';
            if (this.selected.length === this.allIds.length)   return 'All Assistants';
            if (this.selected.length === 1)                    return '1 Assistant Selected';
            return this.selected.length + ' Assistants Selected';
        },

        /* ── dropdown ── */
        openDrop() {
            this.dropOpen = true;
            this.$nextTick(() => this.$refs.searchInput && this.$refs.searchInput.focus());
        },
        closeDrop(submit) {
            this.dropOpen = false;
            this.search   = '';
            if (submit) {
                if (this.selected.length === 0) this.selected = [...this.allIds];
                this.$nextTick(() => document.getElementById('analysis-form').submit());
            }
        },

        /* ── selection ── */
        toggleOne(id) {
            const idx = this.selected.indexOf(id);
            if (idx >= 0) this.selected.splice(idx, 1);
            else          this.selected.push(id);
        },
        toggleFiltered() {
            const ids = this.filtered.map(a => a.id);
            if (this.allFilteredSelected) {
                this.selected = this.selected.filter(id => !ids.includes(id));
            } else {
                ids.forEach(id => { if (!this.selected.includes(id)) this.selected.push(id); });
            }
        },

        /* ── period ── */
        setPeriod(val) {
            this.period = val;
            if (val !== 'custom') this.$nextTick(() => document.getElementById('analysis-form').submit());
        },
    };
}
</script>
@endpush

{{-- ── Controls ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-end gap-3"
     x-data="analysisControls()"
     @keydown.escape.window="if (dropOpen) closeDrop(false)">

    <a href="{{ route('daily-sales.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        ← Daily Grid
    </a>

    <form id="analysis-form" method="GET" action="{{ route('daily-sales.analysis') }}"
          class="flex flex-wrap items-end gap-3">

        {{-- State carriers — always serialised with every submit --}}
        <input type="hidden" name="period"     :value="period">
        <input type="hidden" name="start_date" :value="startDate">
        <input type="hidden" name="end_date"   :value="endDate">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="assistant_ids[]" :value="id">
        </template>

        {{-- ── Searchable multi-select dropdown ─────────────────────────── --}}
        <div class="relative" @click.outside="closeDrop(false)">
            <label class="block text-xs font-medium text-gray-500 mb-1">Assistants</label>

            {{-- Trigger button --}}
            <button type="button"
                    @click="dropOpen ? closeDrop(false) : openDrop()"
                    class="inline-flex items-center justify-between gap-2 rounded-lg border border-gray-200
                           bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition min-w-[210px] h-9">
                <span x-text="label" class="truncate"></span>
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-150"
                     :class="dropOpen ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown panel --}}
            <div x-show="dropOpen"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute z-30 left-0 top-full mt-1 w-72 rounded-xl border border-gray-200
                        bg-white shadow-xl overflow-hidden origin-top-left">

                {{-- Search input --}}
                <div class="p-2 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input x-ref="searchInput"
                               x-model="search"
                               type="text"
                               placeholder="Search assistants…"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 py-1.5
                                      text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    </div>
                </div>

                {{-- Select All / Select All Filtered --}}
                <div class="border-b border-gray-100 bg-gray-50">
                    <label class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-gray-100">
                        <input type="checkbox"
                               :checked="allFilteredSelected"
                               x-effect="$el.indeterminate = someFilteredSelected"
                               @change="toggleFiltered()"
                               class="h-3.5 w-3.5 rounded text-blue-600 border-gray-300">
                        <span class="text-xs font-semibold text-gray-700"
                              x-text="search.trim()
                                  ? 'Select All Filtered (' + filtered.length + ')'
                                  : 'All Assistants (' + allIds.length + ')'">
                        </span>
                    </label>
                </div>

                {{-- Scrollable assistant list --}}
                <div class="max-h-56 overflow-y-auto divide-y divide-gray-50">
                    <template x-for="a in filtered" :key="a.id">
                        <label class="flex items-center gap-2.5 px-3 py-1.5 cursor-pointer hover:bg-blue-50">
                            <input type="checkbox"
                                   :checked="selected.includes(a.id)"
                                   @change="toggleOne(a.id)"
                                   class="h-3.5 w-3.5 rounded text-blue-600 border-gray-300">
                            <span x-text="a.name" class="text-xs text-gray-700 truncate"></span>
                        </label>
                    </template>
                    <div x-show="filtered.length === 0"
                         class="px-3 py-6 text-center text-xs text-gray-400 italic">
                        No assistants match your search.
                    </div>
                </div>

                {{-- Footer: count + Apply --}}
                <div class="border-t border-gray-100 bg-gray-50 px-3 py-2 flex items-center justify-between">
                    <span class="text-xs text-gray-500"
                          x-text="selected.length + ' of ' + allIds.length + ' selected'"></span>
                    <button type="button"
                            @click="closeDrop(true)"
                            class="rounded-lg bg-blue-600 hover:bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white transition">
                        Apply
                    </button>
                </div>
            </div>
        </div>

        {{-- Period pill-group --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Period</label>
            <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                @foreach(['today' => 'Today', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'overall' => 'Overall', 'custom' => 'Custom'] as $val => $label)
                    <button type="button"
                            @click="setPeriod('{{ $val }}')"
                            :class="period === '{{ $val }}' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 font-medium transition whitespace-nowrap">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Custom date range — style= prevents flash before Alpine initialises --}}
        <div x-show="period === 'custom'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="{{ $period === 'custom' ? '' : 'display:none' }}"
             class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                <input type="date"
                       x-model="startDate"
                       :max="endDate"
                       class="erp-input text-sm h-9">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                <input type="date"
                       x-model="endDate"
                       :min="startDate"
                       max="{{ today()->toDateString() }}"
                       class="erp-input text-sm h-9">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 hover:bg-blue-700
                           px-4 py-2 text-sm font-semibold text-white transition h-9">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
        </div>
    </form>
</div>

{{-- ── Info Banner ─────────────────────────────────────────────────────────── --}}
<div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-5 py-3 flex flex-wrap items-center gap-5">

    @if($selectedAssistants->count() === 1)
        @php $solo = $selectedAssistants->first(); @endphp
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white font-bold text-lg">
                {{ strtoupper(substr($solo->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-gray-900">{{ $solo->name }}</p>
                <p class="text-xs text-gray-500">{{ $solo->phone }}</p>
            </div>
        </div>
    @else
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white font-bold text-base">
                {{ $selectedAssistants->count() === count($allIds) ? '★' : $selectedAssistants->count() }}
            </div>
            <div>
                <p class="font-bold text-gray-900">
                    {{ $selectedAssistants->count() === count($allIds) ? 'All Assistants' : $selectedAssistants->count() . ' Assistants' }}
                </p>
                <p class="text-xs text-gray-500">
                    @if($selectedAssistants->count() <= 3)
                        {{ $selectedAssistants->pluck('name')->join(', ') }}
                    @else
                        {{ $selectedAssistants->take(2)->pluck('name')->join(', ') }} + {{ $selectedAssistants->count() - 2 }} more
                    @endif
                </p>
            </div>
        </div>
    @endif

    <div class="h-8 w-px bg-blue-200 hidden sm:block"></div>

    <div>
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Period</p>
        @if($period === 'custom')
            <p class="font-semibold text-gray-800 text-sm">
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                <span class="text-blue-300 mx-1">→</span>
                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </p>
        @else
            <p class="font-semibold text-gray-800 capitalize">{{ ucfirst($period) }}</p>
        @endif
    </div>

    <div>
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Records</p>
        <p class="font-semibold text-gray-800">{{ $stats['recordCount'] }}</p>
    </div>

    @if($selectedAssistants->count() === 1)
    <div class="ml-auto text-right">
        <p class="text-xs text-blue-400 uppercase tracking-wide font-medium">Current Balance</p>
        <p class="text-xl font-bold {{ ($solo->current_balance ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
            Rs. {{ number_format(abs($solo->current_balance ?? 0), 2) }}
        </p>
    </div>
    @endif
</div>

{{-- ── Stats Grid ────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
    @php
        $statCards = [
            ['label'=>'Total Value',      'value'=>$stats['totalValue'],       'color'=>'bg-gray-900 text-white'],
            ['label'=>'Total Cash',       'value'=>$stats['totalCash'],        'color'=>'bg-emerald-600 text-white'],
            ['label'=>'Total Winning',    'value'=>$stats['totalWinning'],     'color'=>'bg-violet-600 text-white'],
            ['label'=>'Total C+W',        'value'=>$stats['totalCW'],          'color'=>'bg-blue-600 text-white'],
            ['label'=>'Credit',           'value'=>$stats['totalOutstanding'], 'color'=>'bg-red-600 text-white'],
        ];
    @endphp
    @foreach($statCards as $c)
        <div class="rounded-xl {{ $c['color'] }} px-4 py-3">
            <p class="text-xs opacity-70 mb-0.5">{{ $c['label'] }}</p>
            <p class="text-lg font-bold">Rs. {{ number_format($c['value'], 0) }}</p>
        </div>
    @endforeach
</div>

{{-- ── Comparison Chart ─────────────────────────────────────────────────── --}}
@if(count($chartLabels) > 0)
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">
            {{ count($assistantIds) > 1 ? 'Assistant Comparison' : 'Performance Overview' }}
        </h3>
        <div class="flex gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-gray-800"></span>Value</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-emerald-500"></span>Cash</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-violet-500"></span>Winning</span>
        </div>
    </div>
    <canvas id="perfChart" style="max-height:260px;"></canvas>
</div>
@endif

{{-- ── Comparison Table (multi-assistant only) ─────────────────────────── --}}
@if($selectedAssistants->count() > 1)
<div class="mb-5 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-gray-700">Assistant Comparison</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Assistant</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-gray-500">Days</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-yellow-600">Value</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-emerald-600">Cash</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-violet-600">Winning</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-cyan-600">C+W</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-gray-500">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($selectedAssistants as $a)
                @php
                    $aS  = $byAssistant->get($a->id) ?? ['totalValue'=>0,'totalCash'=>0,'totalWinning'=>0,'totalCW'=>0,'totalBalance'=>0,'recordCount'=>0];
                    $aBal = $aS['totalBalance'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 font-medium text-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold text-[11px]">
                                {{ strtoupper(substr($a->name, 0, 1)) }}
                            </div>
                            <span>{{ $a->name }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-right text-gray-600">{{ $aS['recordCount'] }}</td>
                    <td class="px-3 py-2.5 text-right text-yellow-700 font-semibold">{{ number_format($aS['totalValue'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($aS['totalCash'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($aS['totalWinning'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-cyan-700">{{ number_format($aS['totalCW'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right">
                        <span class="{{ $aBal > 0 ? 'text-red-700' : ($aBal < 0 ? 'text-amber-700' : 'text-emerald-700') }}">
                            {{ $aBal > 0 ? '+' : '' }}{{ number_format($aBal, 0) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-300 bg-gray-50 font-bold">
                @php $totalBal = $records->sum('balance'); @endphp
                <tr>
                    <td class="px-4 py-2.5 text-gray-700">Totals</td>
                    <td class="px-3 py-2.5 text-right text-gray-900">{{ $stats['recordCount'] }}</td>
                    <td class="px-3 py-2.5 text-right text-yellow-700">{{ number_format($stats['totalValue'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($stats['totalCash'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($stats['totalWinning'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-cyan-700">{{ number_format($stats['totalCW'], 0) }}</td>
                    <td class="px-3 py-2.5 text-right {{ $totalBal > 0 ? 'text-red-700' : ($totalBal < 0 ? 'text-amber-700' : 'text-emerald-700') }}">
                        {{ $totalBal > 0 ? '+' : '' }}{{ number_format($totalBal, 0) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- ── Detailed Records Table ───────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-5 py-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Detailed Records</h3>
        <span class="text-xs text-gray-400">{{ $records->count() }} records</span>
    </div>

    @if($records->isEmpty())
        <div class="py-14 text-center text-sm text-gray-400">No records found for this period.</div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Date</th>
                    @if($selectedAssistants->count() > 1)
                    <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Assistant</th>
                    @endif
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-gray-500">Qty</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-yellow-600">Value</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-emerald-600">Cash</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-violet-600">NLB Win</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-violet-600">DLB Win</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-purple-600">TW</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase text-cyan-600">C+W</th>
                    <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($records->sortByDesc('date') as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 font-medium text-gray-700">{{ $r->date->format('d M Y') }}</td>
                    @if($selectedAssistants->count() > 1)
                    <td class="px-3 py-2.5 text-gray-700">{{ $selectedAssistants->firstWhere('id', $r->assistant_id)?->name ?? '—' }}</td>
                    @endif
                    <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format($r->tickets_issued_qty) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-yellow-700">{{ number_format($r->value, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($r->cash, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($r->nlb_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($r->dlb_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-purple-700">{{ number_format($r->total_winning, 0) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-cyan-700">{{ number_format($r->cw, 0) }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if($r->balance > 0)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">Credit</span>
                        @elseif($r->balance < 0)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Excess</span>
                        @else
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">Settled</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-gray-500 italic">{{ $r->remarks ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-300 font-bold bg-gray-50">
                <tr>
                    <td class="px-4 py-2.5 text-gray-700" colspan="{{ $selectedAssistants->count() > 1 ? 2 : 1 }}">Totals</td>
                    <td class="px-3 py-2.5 text-right text-gray-900">{{ number_format($records->sum('tickets_issued_qty')) }}</td>
                    <td class="px-3 py-2.5 text-right text-yellow-700">{{ number_format($records->sum('value'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">{{ number_format($records->sum('cash'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($records->sum('nlb_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-violet-700">{{ number_format($records->sum('dlb_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-purple-700">{{ number_format($records->sum('total_winning'), 0) }}</td>
                    <td class="px-3 py-2.5 text-right text-cyan-700">{{ number_format($records->sum('cw'), 0) }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @php $tb = $records->sum('balance'); @endphp
                        <span class="{{ $tb > 0 ? 'text-red-700' : ($tb < 0 ? 'text-amber-700' : 'text-green-700') }}">
                            Rs. {{ number_format(abs($tb), 0) }}
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@if(count($chartLabels) > 0)
@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('perfChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels, JSON_HEX_TAG) !!},
            datasets: [
                {
                    label: 'Value',
                    data: {!! json_encode($chartValue) !!},
                    backgroundColor: 'rgba(17,24,39,0.75)',
                    borderRadius: 4,
                },
                {
                    label: 'Cash',
                    data: {!! json_encode($chartCash) !!},
                    backgroundColor: 'rgba(16,185,129,0.8)',
                    borderRadius: 4,
                },
                {
                    label: 'Winning',
                    data: {!! json_encode($chartWinning) !!},
                    backgroundColor: 'rgba(139,92,246,0.8)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => ` ${c.dataset.label}: Rs. ${c.parsed.y.toLocaleString()}`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: (v) => 'Rs. ' + v.toLocaleString() },
                },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
@endpush
@endif

</x-layouts.app>
