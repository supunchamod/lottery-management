<x-layouts.app title="Enter Ticket Distribution">

<div x-data="ticketGrid()" x-init="init()">

{{-- ── Header filters ──────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('ticket-distribution.create') }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Sales Assistant</label>
        <select name="assistant_id" onchange="this.form.submit()"
                class="erp-input text-sm h-9 pr-8">
            @foreach($assistants as $a)
                <option value="{{ $a->id }}" @selected($a->id == $assistantId)>
                    {{ $a->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
        <input type="date" name="date" value="{{ $date }}"
               onchange="this.form.submit()"
               class="erp-input text-sm h-9">
    </div>

    <div class="ml-auto flex items-center gap-2">
        <span class="text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
        </span>
    </div>
</form>

{{-- ── Save form ───────────────────────────────────────────────────────────── --}}
@if($subSellers->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
        <p class="text-sm text-gray-500 mb-3">No active sub-sellers for this assistant.</p>
        <a href="{{ route('ticket-distribution.sub-sellers.index', $assistantId) }}"
           class="text-sm font-medium text-blue-600 hover:underline">
            Add sub-sellers first →
        </a>
    </div>
@else
    <form method="POST" action="{{ route('ticket-distribution.store') }}">
        @csrf
        <input type="hidden" name="assistant_id" value="{{ $assistantId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-xs border-collapse">

                {{-- ── Header ── --}}
                <thead>
                    <tr style="background:#0f172a;">
                        <th class="sticky left-0 z-10 px-3 py-3 text-left text-white font-semibold whitespace-nowrap"
                            style="background:#0f172a; min-width:140px;">#&nbsp; Sub-Seller</th>
                        @foreach($lotteries as $lottery)
                            <th class="px-2 py-3 text-center font-medium whitespace-nowrap
                                       {{ $lottery->board === 'NLB' ? 'text-blue-300' : 'text-orange-300' }}"
                                style="min-width:70px;">
                                <span class="block">{{ $lottery->name }}</span>
                                <span class="block text-[10px] text-gray-500 font-normal">
                                    Rs.{{ number_format($lottery->unit_price, 0) }}
                                </span>
                                <span class="block text-[10px] font-bold text-yellow-400 mt-0.5"
                                      x-text="colTotal({{ $lottery->id }})"></span>
                            </th>
                        @endforeach
                        <th class="px-3 py-3 text-center text-yellow-300 font-semibold">
                            Row Total
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($subSellers as $i => $seller)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors">
                            {{-- Sticky row label --}}
                            <td class="sticky left-0 z-10 px-3 py-2 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                                style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}">
                                <span class="text-gray-400 mr-1">{{ $i + 1 }}</span>
                                {{ $seller->name }}
                            </td>

                            {{-- One input per lottery column --}}
                            @foreach($lotteries as $lottery)
                                @php
                                    $existing_qty = $existing
                                        ->get($seller->id, collect())
                                        ->get($lottery->id, '');
                                @endphp
                                <td class="px-1.5 py-1.5 text-center">
                                    <input
                                        type="number"
                                        name="qty[{{ $seller->id }}][{{ $lottery->id }}]"
                                        value="{{ $existing_qty ?: '' }}"
                                        min="0"
                                        placeholder="0"
                                        x-model.number="grid[{{ $seller->id }}][{{ $lottery->id }}]"
                                        @input="updateTotals()"
                                        class="w-14 rounded border border-gray-200 px-1 py-1 text-center text-xs
                                               focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300
                                               [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    >
                                </td>
                            @endforeach

                            {{-- Row total --}}
                            <td class="px-3 py-2 text-center font-bold text-blue-700">
                                <span x-text="rowTotal({{ $seller->id }})"></span>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Footer totals row --}}
                    <tr class="border-t-2 border-gray-300 bg-slate-800">
                        <td class="sticky left-0 z-10 px-3 py-3 font-bold text-white border-r border-slate-600"
                            style="background:#1e293b;">Column Totals</td>
                        @foreach($lotteries as $lottery)
                            <td class="px-2 py-3 text-center font-bold text-yellow-300">
                                <span x-text="colTotal({{ $lottery->id }})"></span>
                            </td>
                        @endforeach
                        <td class="px-3 py-3 text-center font-bold text-yellow-300">
                            <span x-text="grandTotal()"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Action buttons --}}
        <div class="mt-4 flex items-center justify-between">
            <a href="{{ route('ticket-distribution.index', ['assistant_id' => $assistantId, 'date' => $date]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                ← Back to View
            </a>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">
                    Grand Total: <span class="font-bold text-gray-900" x-text="grandTotal()"></span> tickets
                </span>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Distribution
                </button>
            </div>
        </div>
    </form>
@endif

</div>{{-- end x-data --}}

@push('scripts')
<script>
function ticketGrid() {
    return {
        grid: {},

        init() {
            // Bootstrap the reactive grid from server-rendered values
            @foreach($subSellers as $seller)
            this.grid[{{ $seller->id }}] = {};
            @foreach($lotteries as $lottery)
            @php
                $val = $existing->get($seller->id, collect())->get($lottery->id, 0);
            @endphp
            this.grid[{{ $seller->id }}][{{ $lottery->id }}] = {{ (int)$val }};
            @endforeach
            @endforeach
        },

        updateTotals() {
            // Alpine reactivity handles re-render automatically
        },

        rowTotal(sellerId) {
            const row = this.grid[sellerId] || {};
            return Object.values(row).reduce((s, v) => s + (parseInt(v) || 0), 0);
        },

        colTotal(lotteryId) {
            let total = 0;
            for (const sellerId in this.grid) {
                total += parseInt(this.grid[sellerId][lotteryId]) || 0;
            }
            return total;
        },

        grandTotal() {
            let total = 0;
            for (const sellerId in this.grid) {
                for (const lotteryId in this.grid[sellerId]) {
                    total += parseInt(this.grid[sellerId][lotteryId]) || 0;
                }
            }
            return total;
        },
    };
}
</script>
@endpush

</x-layouts.app>
