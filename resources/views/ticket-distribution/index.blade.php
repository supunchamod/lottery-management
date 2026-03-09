<x-layouts.app title="Ticket Distribution">

{{-- ── Top filter bar ──────────────────────────────────────────────────────── --}}
<div class="mb-4 flex flex-wrap items-end gap-3">
    <form method="GET" action="{{ route('ticket-distribution.index') }}"
          class="flex flex-wrap items-end gap-3">

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
    </form>

    <div class="ml-auto flex gap-2">
        @if($assistant)
        <a href="{{ route('ticket-distribution.create', ['assistant_id' => $assistantId, 'date' => $date]) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Enter / Edit Tickets
        </a>
        <a href="{{ route('ticket-distribution.sub-sellers.index', $assistant->id) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Manage Sub-sellers
        </a>
        @endif
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition print:hidden">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>
</div>

{{-- ── Distribution Grid ───────────────────────────────────────────────────── --}}
@if($subSellers->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="mt-3 text-sm text-gray-500">No sub-sellers found for this assistant.</p>
        <a href="{{ route('ticket-distribution.sub-sellers.index', $assistantId) }}"
           class="mt-3 inline-block text-sm font-medium text-blue-600 hover:underline">
            Add sub-sellers →
        </a>
    </div>
@else
    {{-- Header info bar --}}
    <div class="mb-3 flex items-center gap-4 rounded-lg border border-blue-100 bg-blue-50 px-4 py-2.5">
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-blue-500 uppercase tracking-wide">Date</span>
            <span class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}
            </span>
            <span class="text-gray-400">·</span>
            <span class="text-gray-700">{{ \Carbon\Carbon::parse($date)->format('l') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-blue-500 uppercase tracking-wide">Assistant</span>
            <span class="font-semibold text-gray-900">{{ $assistant?->name ?? '—' }}</span>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <span class="text-xs font-medium text-gray-400">Grand Total</span>
            <span class="rounded-full bg-blue-600 px-3 py-0.5 text-sm font-bold text-white">
                {{ number_format($grandTotal) }}
            </span>
        </div>
    </div>

    {{-- Scrollable grid --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-xs border-collapse">
            {{-- Column headers --}}
            <thead>
                <tr style="background:#0f172a;">
                    <th class="sticky left-0 z-10 px-3 py-2.5 text-left text-white font-medium whitespace-nowrap"
                        style="background:#0f172a; min-width:130px;">#&nbsp; Sub-Seller</th>
                    @foreach($lotteries as $lottery)
                        <th class="px-2 py-2.5 text-center font-medium whitespace-nowrap
                                   {{ $lottery->board === 'NLB' ? 'text-blue-300' : 'text-orange-300' }}"
                            style="min-width:60px;">
                            {{ $lottery->name }}
                            <span class="block text-gray-500 font-normal text-[10px]">
                                Rs.{{ number_format($lottery->unit_price, 0) }}
                            </span>
                        </th>
                    @endforeach
                    <th class="px-3 py-2.5 text-center text-yellow-300 font-medium whitespace-nowrap">
                        Total
                    </th>
                </tr>

                {{-- "Add Value" row = column subtotals label --}}
                <tr class="bg-slate-700 border-b border-slate-600">
                    <td class="sticky left-0 z-10 px-3 py-1.5 text-slate-300 font-medium text-[11px]"
                        style="background:#334155;">Add Value</td>
                    @foreach($lotteries as $lottery)
                        <td class="px-2 py-1.5 text-center text-slate-200 font-semibold">
                            {{ number_format($colTotals[$lottery->id] ?? 0) }}
                        </td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center text-yellow-300 font-bold">
                        {{ number_format($grandTotal) }}
                    </td>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @foreach($subSellers as $i => $seller)
                    @php
                        $sellerRow = $distributions->get($seller->id, collect());
                        $rowTotal  = $rowTotals[$seller->id] ?? 0;
                    @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors">
                        <td class="sticky left-0 z-10 px-3 py-2 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                            style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}">
                            <span class="mr-1.5 text-gray-400">{{ $i + 1 }}</span>
                            {{ $seller->name }}
                        </td>
                        @foreach($lotteries as $lottery)
                            @php $qty = $sellerRow->get($lottery->id, 0); @endphp
                            <td class="px-2 py-2 text-center {{ $qty > 0 ? 'text-gray-900 font-medium' : 'text-gray-300' }}">
                                {{ $qty > 0 ? number_format($qty) : '' }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2 text-center font-bold
                                   {{ $rowTotal > 0 ? 'text-blue-700' : 'text-gray-300' }}">
                            {{ $rowTotal > 0 ? number_format($rowTotal) : '0' }}
                        </td>
                    </tr>
                @endforeach

                {{-- Footer totals row --}}
                <tr class="border-t-2 border-gray-300 bg-gray-100 font-bold">
                    <td class="sticky left-0 z-10 px-3 py-2.5 text-gray-700 border-r border-gray-200"
                        style="background:#f3f4f6;">Total</td>
                    @foreach($lotteries as $lottery)
                        <td class="px-2 py-2.5 text-center text-gray-900">
                            {{ number_format($colTotals[$lottery->id] ?? 0) }}
                        </td>
                    @endforeach
                    <td class="px-3 py-2.5 text-center text-blue-700">
                        {{ number_format($grandTotal) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif

@push('head')
<style>
@media print {
    .print\:hidden { display: none !important; }
    aside, header { display: none !important; }
    .ml-64 { margin-left: 0 !important; }
    body { font-size: 10px; }
}
</style>
@endpush

</x-layouts.app>
