<x-layouts.app title="Distribution Summary">

@php use Carbon\Carbon; @endphp

{{-- ── Controls ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-end gap-3 print:hidden">

    <a href="{{ route('ticket-distribution.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        ← Grid Entry
    </a>

    <form method="GET" action="{{ route('ticket-distribution.summary') }}"
          class="flex flex-wrap items-end gap-3">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">View</label>
            <select name="mode" onchange="this.form.submit()" class="erp-input text-sm h-9 pr-8">
                <option value="weekly"  @selected($mode === 'weekly')>Weekly</option>
                <option value="monthly" @selected($mode === 'monthly')>Monthly</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">
                {{ $mode === 'weekly' ? 'Any date in the week' : 'Any date in the month' }}
            </label>
            <input type="date" name="ref" value="{{ $reference }}"
                   onchange="this.form.submit()" class="erp-input text-sm h-9">
        </div>
    </form>

    <div class="ml-auto flex items-center gap-3">
        {{-- Prev / Next navigation --}}
        @php
            $prevRef = $mode === 'weekly'
                ? Carbon::parse($reference)->subWeek()->toDateString()
                : Carbon::parse($reference)->subMonth()->toDateString();
            $nextRef = $mode === 'weekly'
                ? Carbon::parse($reference)->addWeek()->toDateString()
                : Carbon::parse($reference)->addMonth()->toDateString();
        @endphp
        <a href="{{ route('ticket-distribution.summary', ['mode' => $mode, 'ref' => $prevRef]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">‹ Prev</a>
        <a href="{{ route('ticket-distribution.summary', ['mode' => $mode, 'ref' => $nextRef]) }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">Next ›</a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Print
        </button>
    </div>
</div>

{{-- ── Header info ───────────────────────────────────────────────────────── --}}
<div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-5 py-3 flex flex-wrap items-center gap-5">
    <div>
        <p class="text-xs font-medium text-blue-400 uppercase tracking-wide">Period</p>
        <p class="font-bold text-gray-900">{{ $label }}</p>
    </div>
    <div>
        <p class="text-xs font-medium text-blue-400 uppercase tracking-wide">Range</p>
        <p class="font-semibold text-gray-700">
            {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        </p>
    </div>
    <div class="ml-auto text-right">
        <p class="text-xs font-medium text-blue-400 uppercase tracking-wide">Grand Total</p>
        <p class="text-2xl font-bold text-blue-700">{{ number_format($grandTotal) }}</p>
    </div>
</div>

@if($lotteries->isEmpty() || $assistants->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center text-sm text-gray-400">
        No data to display.
    </div>
@else

{{-- ── Summary Table ─────────────────────────────────────────────────────── --}}
<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full border-collapse text-xs">

        {{-- Header --}}
        <thead>
            <tr style="background:#0f172a;">
                <th class="sticky left-0 z-20 px-4 py-3 text-left text-white font-medium whitespace-nowrap border-r border-slate-700"
                    style="background:#0f172a; min-width:160px;">
                    # &nbsp; Assistant
                </th>
                @foreach($lotteries as $l)
                    <th class="px-3 py-3 text-center font-medium whitespace-nowrap
                               {{ $l->board === 'NLB' ? 'text-blue-300' : 'text-orange-300' }}"
                        style="min-width:68px;">
                        {{ $l->name }}
                        <span class="block text-gray-500 font-normal" style="font-size:10px;">{{ $l->board }}</span>
                    </th>
                @endforeach
                <th class="px-4 py-3 text-center text-yellow-300 font-semibold whitespace-nowrap">
                    Total
                </th>
            </tr>

            {{-- Column totals top --}}
            <tr class="border-b-2 border-slate-600" style="background:#1e293b;">
                <td class="sticky left-0 z-20 px-4 py-2 text-slate-300 font-semibold text-[11px] border-r border-slate-600"
                    style="background:#1e293b;">Total ↓</td>
                @foreach($lotteries as $l)
                    <td class="px-3 py-2 text-center text-slate-100 font-bold">
                        {{ number_format($colTotals[$l->id] ?? 0) }}
                    </td>
                @endforeach
                <td class="px-4 py-2 text-center text-yellow-300 font-bold">
                    {{ number_format($grandTotal) }}
                </td>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
            @foreach($assistants as $i => $a)
                @php
                    $rowTotal = $rowTotals[$a->id] ?? 0;
                    $isActive = $rowTotal > 0;
                @endphp
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/60' }} hover:bg-blue-50 transition-colors
                            {{ !$isActive ? 'opacity-40' : '' }}">
                    <td class="sticky left-0 z-10 px-4 py-2.5 font-medium text-gray-700 whitespace-nowrap border-r border-gray-200"
                        style="{{ $i % 2 === 0 ? 'background:#fff' : 'background:#f9fafb' }}">
                        <span class="mr-1.5 text-gray-400 text-[11px]">{{ $i + 1 }}</span>
                        {{ $a->name }}
                    </td>
                    @foreach($lotteries as $l)
                        @php $qty = $summary[$a->id][$l->id] ?? 0; @endphp
                        <td class="px-3 py-2.5 text-center {{ $qty > 0 ? 'text-gray-900 font-semibold' : 'text-gray-200' }}">
                            {{ $qty > 0 ? number_format($qty) : '—' }}
                        </td>
                    @endforeach
                    <td class="px-4 py-2.5 text-center font-bold
                               {{ $rowTotal > 0 ? 'text-blue-700' : 'text-gray-300' }}">
                        {{ $rowTotal > 0 ? number_format($rowTotal) : '0' }}
                    </td>
                </tr>
            @endforeach

            {{-- Bottom total --}}
            <tr class="border-t-2 border-gray-300 font-bold" style="background:#f1f5f9;">
                <td class="sticky left-0 z-10 px-4 py-3 text-gray-700 border-r border-gray-200"
                    style="background:#f1f5f9;">Total ↑</td>
                @foreach($lotteries as $l)
                    <td class="px-3 py-3 text-center text-gray-900">
                        {{ number_format($colTotals[$l->id] ?? 0) }}
                    </td>
                @endforeach
                <td class="px-4 py-3 text-center text-blue-700">
                    {{ number_format($grandTotal) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Per-assistant performance bar chart ──────────────────────────────── --}}
<div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm print:hidden">
    <h3 class="mb-4 text-sm font-semibold text-gray-700">Assistant Performance</h3>
    @php $maxRow = max(array_merge([1], array_values($rowTotals))); @endphp
    <div class="space-y-2">
        @foreach($assistants->sortByDesc(fn($a) => $rowTotals[$a->id] ?? 0)->take(20) as $a)
            @php $rt = $rowTotals[$a->id] ?? 0; $pct = $rt > 0 ? round($rt / $maxRow * 100) : 0; @endphp
            @if($rt > 0)
            <div class="flex items-center gap-3">
                <span class="w-36 truncate text-xs font-medium text-gray-700 text-right">{{ $a->name }}</span>
                <div class="flex-1 h-5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-5 rounded-full bg-blue-500 transition-all"
                         style="width: {{ $pct }}%"></div>
                </div>
                <span class="w-16 text-xs font-bold text-gray-700 text-right">{{ number_format($rt) }}</span>
            </div>
            @endif
        @endforeach
    </div>
</div>

@endif

@push('head')
<style>
@media print {
    .print\:hidden { display: none !important; }
    aside, header { display: none !important; }
    .overflow-x-auto { overflow: visible !important; }
}
</style>
@endpush

</x-layouts.app>
