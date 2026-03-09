<x-layouts.app title="Board Settlement">

{{-- ══════════════════════════════════════════════════════════════════════════
     ALPINE.JS DATA COMPONENT
     Encapsulates ALL reactive state and calculations for the form.
════════════════════════════════════════════════════════════════════════════ --}}
<div x-data="boardSettlement()" x-init="init()" class="space-y-6">

{{-- ── Page header ────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Board Settlement Entry</h2>
        <p class="text-sm text-gray-500 mt-0.5">NLB · DLB — Daily Ticket Value & Winning Analysis</p>
    </div>
    {{-- Date navigator --}}
    <form method="GET" action="{{ route('board-settlement.index') }}" class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-600">Date:</label>
        <input type="date" name="date" value="{{ $date }}"
               class="erp-input text-sm w-40"
               onchange="this.form.submit()">
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SETTLEMENT FORM
════════════════════════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('board-settlement.store') }}" @submit="syncHiddenFields">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    {{-- Hidden computed fields written by Alpine before submit --}}
    <input type="hidden" name="nlb_total"     x-ref="h_nlb_total">
    <input type="hidden" name="dlb_total"     x-ref="h_dlb_total">
    <input type="hidden" name="total_winning" x-ref="h_total_winning">
    <input type="hidden" name="cash_total"    x-ref="h_cash_total">
    <input type="hidden" name="total_paid"    x-ref="h_total_paid">
    <input type="hidden" name="balance"       x-ref="h_balance">

    {{-- ── Row 1: Inventory summary card ──────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Inventory</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Total Tickets Received</label>
                <input type="number" name="total_tickets_received" min="0"
                       value="{{ old('total_tickets_received', $settlement?->total_tickets_received ?? '') }}"
                       class="erp-input w-full" placeholder="0">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Total Ticket Value (Rs.)</label>
                <input type="number" name="total_ticket_value" min="0" step="0.01"
                       x-model.number="ticketValue"
                       value="{{ old('total_ticket_value', $settlement?->total_ticket_value ?? '') }}"
                       class="erp-input w-full font-semibold" placeholder="0.00">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bank Deposits (Rs.)</label>
                <input type="number" name="bank_deposits" min="0" step="0.01"
                       x-model.number="bankDeposits"
                       value="{{ old('bank_deposits', $settlement?->bank_deposits ?? '') }}"
                       class="erp-input w-full" placeholder="0.00">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" maxlength="500"
                       value="{{ old('notes', $settlement?->notes ?? '') }}"
                       class="erp-input w-full" placeholder="Optional note…">
            </div>
        </div>
    </div>

    {{-- ── Row 2: Two-column — Left: Winning Analysis | Right: Cash + Summary ──  --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ════════════ LEFT COLUMN: NLB + DLB Winning Analysis ════════════ --}}
        <div class="space-y-4">

            {{-- NLB Winning Analysis --}}
            <div class="rounded-xl border border-blue-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-blue-600 px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm font-bold text-white tracking-wide">NLB — Winning Analysis</span>
                    <span class="text-blue-100 text-xs font-medium">National Lottery Board</span>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left pb-2 text-xs text-gray-500 font-medium w-24">Denomination</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">×</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium">Qty</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">=</th>
                                <th class="text-right pb-2 text-xs text-gray-500 font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach(\App\Models\BoardSettlement::NLB_TIERS as $col => $denom)
                            <tr>
                                <td class="py-1.5 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                <td class="py-1.5 text-center text-gray-400">×</td>
                                <td class="py-1">
                                    <input type="number" name="{{ $col }}" min="0"
                                           x-model.number="nlb['{{ $col }}']"
                                           @input="calcNlb"
                                           value="{{ old($col, $settlement?->{$col} ?? '') }}"
                                           class="w-full text-center border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                           placeholder="0">
                                </td>
                                <td class="py-1.5 text-center text-gray-400">=</td>
                                <td class="py-1.5 text-right font-medium text-gray-800"
                                    x-text="fmt(nlb['{{ $col }}'] * {{ $denom }})">0</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-blue-200 bg-blue-50">
                                <td colspan="4" class="py-2 pl-2 text-sm font-bold text-blue-800">NLB Total</td>
                                <td class="py-2 pr-1 text-right text-sm font-bold text-blue-800"
                                    x-text="fmt(nlbTotal)">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- DLB Winning Analysis --}}
            <div class="rounded-xl border border-purple-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-purple-600 px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm font-bold text-white tracking-wide">DLB — Winning Analysis</span>
                    <span class="text-purple-100 text-xs font-medium">Development Lottery Board</span>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left pb-2 text-xs text-gray-500 font-medium w-24">Denomination</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">×</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium">Qty</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">=</th>
                                <th class="text-right pb-2 text-xs text-gray-500 font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach(\App\Models\BoardSettlement::DLB_TIERS as $col => $denom)
                            <tr>
                                <td class="py-1.5 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                <td class="py-1.5 text-center text-gray-400">×</td>
                                <td class="py-1">
                                    <input type="number" name="{{ $col }}" min="0"
                                           x-model.number="dlb['{{ $col }}']"
                                           @input="calcDlb"
                                           value="{{ old($col, $settlement?->{$col} ?? '') }}"
                                           class="w-full text-center border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-purple-400 focus:border-purple-400 outline-none"
                                           placeholder="0">
                                </td>
                                <td class="py-1.5 text-center text-gray-400">=</td>
                                <td class="py-1.5 text-right font-medium text-gray-800"
                                    x-text="fmt(dlb['{{ $col }}'] * {{ $denom }})">0</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-purple-200 bg-purple-50">
                                <td colspan="4" class="py-2 pl-2 text-sm font-bold text-purple-800">DLB Total</td>
                                <td class="py-2 pr-1 text-right text-sm font-bold text-purple-800"
                                    x-text="fmt(dlbTotal)">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Grand Winning total spanning both boards --}}
            <div class="rounded-xl border-2 border-indigo-300 bg-indigo-50 px-5 py-4 flex items-center justify-between">
                <span class="text-base font-bold text-indigo-800">Grand Total Winning (NLB + DLB)</span>
                <span class="text-2xl font-extrabold text-indigo-700" x-text="fmt(grandWinning)">0</span>
            </div>
        </div>

        {{-- ════════════ RIGHT COLUMN: Cash Counter + Payment Summary ════════ --}}
        <div class="space-y-4">

            {{-- Cash Counter --}}
            <div class="rounded-xl border border-green-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-green-600 px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm font-bold text-white tracking-wide">Cash Counter</span>
                    <span class="text-green-100 text-xs font-medium">Physical cash denomination breakdown</span>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left pb-2 text-xs text-gray-500 font-medium w-24">Denomination</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">×</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium">Qty</th>
                                <th class="text-center pb-2 text-xs text-gray-500 font-medium w-8">=</th>
                                <th class="text-right pb-2 text-xs text-gray-500 font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach(\App\Models\BoardSettlement::CASH_DENOMS as $col => $denom)
                            <tr>
                                <td class="py-1.5 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                <td class="py-1.5 text-center text-gray-400">×</td>
                                <td class="py-1">
                                    <input type="number" name="{{ $col }}" min="0"
                                           x-model.number="cash['{{ $col }}']"
                                           @input="calcCash"
                                           value="{{ old($col, $settlement?->{$col} ?? '') }}"
                                           class="w-full text-center border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-400 focus:border-green-400 outline-none"
                                           placeholder="0">
                                </td>
                                <td class="py-1.5 text-center text-gray-400">=</td>
                                <td class="py-1.5 text-right font-medium text-gray-800"
                                    x-text="fmt(cash['{{ $col }}'] * {{ $denom }})">0</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-green-200 bg-green-50">
                                <td colspan="4" class="py-2 pl-2 text-sm font-bold text-green-800">Cash Total</td>
                                <td class="py-2 pr-1 text-right text-sm font-bold text-green-800"
                                    x-text="fmt(cashTotal)">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Payment Summary card --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-gray-800 px-4 py-2.5">
                    <span class="text-sm font-bold text-white tracking-wide">Payment Summary</span>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Total Ticket Value</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="fmt(ticketValue)">0</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Total Winning (NLB + DLB)</span>
                        <span class="text-sm font-semibold text-indigo-700" x-text="fmt(grandWinning)">0</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Cash Paid</span>
                        <span class="text-sm font-semibold text-green-700" x-text="fmt(cashTotal)">0</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Bank Deposits</span>
                        <span class="text-sm font-semibold text-gray-700" x-text="fmt(bankDeposits)">0</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 bg-gray-50 -mx-5 px-5">
                        <span class="text-sm font-bold text-gray-700">Total Paid</span>
                        <span class="text-base font-bold text-gray-900" x-text="fmt(totalPaid)">0</span>
                    </div>
                    <div class="flex items-center justify-between pt-3">
                        <span class="text-base font-bold"
                              :class="balance >= 0 ? 'text-red-700' : 'text-green-700'">
                            <span x-text="balance >= 0 ? 'Outstanding Balance (Credit)' : 'Overpaid (Excess)'"></span>
                        </span>
                        <span class="text-2xl font-extrabold"
                              :class="balance >= 0 ? 'text-red-600' : 'text-green-600'"
                              x-text="fmt(Math.abs(balance))">0</span>
                    </div>
                    <div class="pt-1">
                        <div class="w-full rounded-full h-2 bg-gray-200 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-500"
                                 :class="balance <= 0 ? 'bg-green-500' : 'bg-red-400'"
                                 :style="`width: ${ticketValue > 0 ? Math.min(100, (totalPaid/ticketValue)*100) : 0}%`">
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 text-right"
                           x-text="ticketValue > 0 ? Math.round((totalPaid/ticketValue)*100) + '% settled' : ''"></p>
                    </div>
                </div>
            </div>

            {{-- Grand Total box matching image --}}
            <div class="rounded-xl border-2 border-gray-800 bg-gray-900 px-5 py-4 text-center">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Grand Total Paid</p>
                <p class="text-3xl font-extrabold text-white" x-text="fmt(totalPaid)">0</p>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                           text-white font-bold text-sm py-3.5 tracking-wide transition-colors shadow-sm">
                Save Settlement for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            </button>
        </div>
    </div>
</form>

{{-- ══════════════════════════════════════════════════════════════════════════
     HISTORY TABLE — Board Transaction Ledger
════════════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
        <div>
            <h3 class="text-base font-bold text-gray-900">Board Transaction History</h3>
            <p class="text-xs text-gray-500 mt-0.5">Running balance · {{ $history->total() }} records</p>
        </div>
        {{-- Filters --}}
        <form method="GET" action="{{ route('board-settlement.index') }}"
              class="flex flex-wrap gap-2 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500 font-medium">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="erp-input text-xs w-36">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500 font-medium">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="erp-input text-xs w-36">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500 font-medium">Status</label>
                <select name="status" class="erp-input text-xs w-32">
                    <option value="">All</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="settled"  {{ request('status') === 'settled'  ? 'selected' : '' }}>Settled</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 text-white text-xs font-semibold px-3 py-2 hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('board-settlement.index') }}"
                   class="rounded-lg border border-gray-300 text-gray-600 text-xs font-medium px-3 py-2 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left">#</th>
                    <th class="text-left">Date</th>
                    <th class="text-right">Ticket Value</th>
                    <th class="text-right">NLB Winning</th>
                    <th class="text-right">DLB Winning</th>
                    <th class="text-right">Total Winning</th>
                    <th class="text-right">Cash Paid</th>
                    <th class="text-right">Bank Dep.</th>
                    <th class="text-right">Total Paid</th>
                    <th class="text-right">Balance</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyItems as $idx => $row)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="text-gray-400 text-xs">{{ $history->firstItem() + $idx }}</td>
                    <td class="font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                    </td>
                    <td class="text-right">{{ number_format($row['total_ticket_value'], 2) }}</td>
                    <td class="text-right text-blue-700">{{ number_format($row['nlb_total'], 2) }}</td>
                    <td class="text-right text-purple-700">{{ number_format($row['dlb_total'], 2) }}</td>
                    <td class="text-right font-semibold text-indigo-700">{{ number_format($row['total_winning'], 2) }}</td>
                    <td class="text-right text-green-700">{{ number_format($row['cash_total'], 2) }}</td>
                    <td class="text-right text-gray-600">{{ number_format($row['bank_deposits'], 2) }}</td>
                    <td class="text-right font-semibold">{{ number_format($row['total_paid'], 2) }}</td>
                    <td class="text-right font-bold
                        {{ $row['balance'] > 0 ? 'text-red-600' : ($row['balance'] < 0 ? 'text-green-600' : 'text-gray-500') }}">
                        {{ number_format(abs($row['balance']), 2) }}
                        @if($row['balance'] > 0) CR @elseif($row['balance'] < 0) OVR @endif
                    </td>
                    <td class="text-center">
                        @if($row['balance'] <= 0)
                            <span class="inline-flex rounded-full bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5">Settled</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5">Pending</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('board-settlement.index', ['date' => $row['date']]) }}"
                           class="text-xs text-blue-600 hover:underline font-medium">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="py-10 text-center text-gray-400 text-sm">
                        No settlement records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($historyItems->isNotEmpty())
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr class="font-bold text-gray-800 text-sm">
                    <td colspan="2" class="py-3 pl-4">Page Total</td>
                    <td class="text-right py-3">{{ number_format($historyItems->sum('total_ticket_value'), 2) }}</td>
                    <td class="text-right text-blue-700 py-3">{{ number_format($historyItems->sum('nlb_total'), 2) }}</td>
                    <td class="text-right text-purple-700 py-3">{{ number_format($historyItems->sum('dlb_total'), 2) }}</td>
                    <td class="text-right text-indigo-700 py-3">{{ number_format($historyItems->sum('total_winning'), 2) }}</td>
                    <td class="text-right text-green-700 py-3">{{ number_format($historyItems->sum('cash_total'), 2) }}</td>
                    <td class="text-right py-3">{{ number_format($historyItems->sum('bank_deposits'), 2) }}</td>
                    <td class="text-right py-3">{{ number_format($historyItems->sum('total_paid'), 2) }}</td>
                    <td class="text-right py-3">{{ number_format($historyItems->sum('balance'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($history->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $history->links() }}
    </div>
    @endif
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function boardSettlement() {
    // ── Seed existing values from PHP (for edit mode) ────────────────────────
    const existing = @json($settlement ?? null);

    function seedInt(key)   { return existing ? (parseInt(existing[key]) || 0) : 0; }
    function seedFloat(key) { return existing ? (parseFloat(existing[key]) || 0) : 0; }

    return {
        // ── Reactive state ──────────────────────────────────────────────────
        ticketValue:  seedFloat('total_ticket_value'),
        bankDeposits: seedFloat('bank_deposits'),

        nlb: {
            @foreach(\App\Models\BoardSettlement::NLB_TIERS as $col => $denom)
            '{{ $col }}': seedInt('{{ $col }}'),
            @endforeach
        },
        dlb: {
            @foreach(\App\Models\BoardSettlement::DLB_TIERS as $col => $denom)
            '{{ $col }}': seedInt('{{ $col }}'),
            @endforeach
        },
        cash: {
            @foreach(\App\Models\BoardSettlement::CASH_DENOMS as $col => $denom)
            '{{ $col }}': seedInt('{{ $col }}'),
            @endforeach
        },

        // ── Computed totals ─────────────────────────────────────────────────
        nlbTotal:     0,
        dlbTotal:     0,
        grandWinning: 0,
        cashTotal:    0,
        totalPaid:    0,
        balance:      0,

        // ── Init ────────────────────────────────────────────────────────────
        init() {
            this.calcNlb();
            this.calcDlb();
            this.calcCash();
        },

        // ── NLB calc ────────────────────────────────────────────────────────
        calcNlb() {
            const tiers = @json(\App\Models\BoardSettlement::NLB_TIERS);
            let total = 0;
            for (const [col, denom] of Object.entries(tiers)) {
                total += (parseInt(this.nlb[col]) || 0) * denom;
            }
            this.nlbTotal = total;
            this.calcSummary();
        },

        // ── DLB calc ────────────────────────────────────────────────────────
        calcDlb() {
            const tiers = @json(\App\Models\BoardSettlement::DLB_TIERS);
            let total = 0;
            for (const [col, denom] of Object.entries(tiers)) {
                total += (parseInt(this.dlb[col]) || 0) * denom;
            }
            this.dlbTotal = total;
            this.calcSummary();
        },

        // ── Cash calc ───────────────────────────────────────────────────────
        calcCash() {
            const denoms = @json(\App\Models\BoardSettlement::CASH_DENOMS);
            let total = 0;
            for (const [col, denom] of Object.entries(denoms)) {
                total += (parseInt(this.cash[col]) || 0) * denom;
            }
            this.cashTotal = total;
            this.calcSummary();
        },

        // ── Summary calc ────────────────────────────────────────────────────
        calcSummary() {
            this.grandWinning = this.nlbTotal + this.dlbTotal;
            this.totalPaid    = this.grandWinning + this.cashTotal + (parseFloat(this.bankDeposits) || 0);
            this.balance      = (parseFloat(this.ticketValue) || 0) - this.totalPaid;
        },

        // ── Write computed values to hidden inputs before form submit ────────
        syncHiddenFields() {
            this.$refs.h_nlb_total.value     = this.nlbTotal;
            this.$refs.h_dlb_total.value     = this.dlbTotal;
            this.$refs.h_total_winning.value = this.grandWinning;
            this.$refs.h_cash_total.value    = this.cashTotal;
            this.$refs.h_total_paid.value    = this.totalPaid;
            this.$refs.h_balance.value       = this.balance;
        },

        // ── Number formatter ────────────────────────────────────────────────
        fmt(n) {
            return Number(n || 0).toLocaleString('en-LK', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            });
        },
    };
}
</script>
@endpush

</x-layouts.app>
