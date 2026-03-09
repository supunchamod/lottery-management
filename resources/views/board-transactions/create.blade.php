<x-layouts.app title="New Board Transaction">

<div x-data="boardTxForm()" x-init="init()" class="max-w-5xl mx-auto space-y-5">

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 mb-2">
    <a href="{{ route('board-transactions.index') }}"
       class="rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 p-2 transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-900">New Board Transaction</h2>
        <p class="text-sm text-gray-500">Record a ticket receipt, bill payment, or credit adjustment</p>
    </div>
</div>

<form method="POST" action="{{ route('board-transactions.store') }}" @submit="prepareSubmit">
    @csrf

    {{-- Hidden fields written by Alpine before submit --}}
    <input type="hidden" name="winning_amount" x-ref="h_winning">
    <input type="hidden" name="cash_amount"    x-ref="h_cash">

    {{-- ══════════════════════════════════════════════════════════════════════
         TOP: Transaction type + date
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Transaction Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- Type selector --}}
            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Transaction Type *</label>
                <select name="description" x-model="type"
                        class="erp-input w-full font-semibold">
                    <option value="get_tickets">Get Tickets</option>
                    <option value="paid_bill">Paid Bill</option>
                    <option value="credit">Credit</option>
                </select>
            </div>

            {{-- Date 01 --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date 01 *</label>
                <input type="date" name="date"
                       value="{{ old('date', today()->toDateString()) }}"
                       class="erp-input w-full" required>
            </div>

            {{-- Date 02 (optional, for Get Tickets delivery date) --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Date 02 <span class="text-gray-400">(optional)</span>
                </label>
                <input type="date" name="date_02"
                       value="{{ old('date_02') }}"
                       class="erp-input w-full">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" maxlength="500"
                       value="{{ old('notes') }}"
                       class="erp-input w-full" placeholder="Optional…">
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         GET TICKETS section
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="type === 'get_tickets'"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0">

        <div class="rounded-xl border border-blue-200 bg-white shadow-sm p-5">
            <h3 class="text-sm font-semibold text-blue-700 uppercase tracking-wide mb-4">
                Ticket Inventory
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Ticket Quantity (Amt)</label>
                    <input type="number" name="ticket_qty" min="0"
                           value="{{ old('ticket_qty') }}"
                           class="erp-input w-full text-lg font-semibold" placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Total Ticket Value (Rs.)</label>
                    <input type="number" name="ticket_value" min="0" step="0.01"
                           x-model.number="ticketValue"
                           value="{{ old('ticket_value') }}"
                           class="erp-input w-full text-lg font-bold text-blue-700" placeholder="0.00">
                </div>
                <div class="flex items-end">
                    <div class="w-full rounded-lg bg-blue-50 border border-blue-200 p-3 text-center">
                        <p class="text-xs text-blue-500 mb-1">Will add to balance</p>
                        <p class="text-xl font-extrabold text-blue-700"
                           x-text="fmt(ticketValue)">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         PAID BILL section — NLB + DLB winning + Cash
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="type === 'paid_bill'"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- LEFT: Winning Analysis (NLB + DLB) --}}
            <div class="space-y-4">

                {{-- NLB Winning --}}
                <div class="rounded-xl border border-blue-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-blue-600 px-4 py-2.5 flex justify-between items-center">
                        <span class="text-sm font-bold text-white">NLB — Winning</span>
                        <span class="text-blue-100 text-xs">National Lottery Board</span>
                    </div>
                    <div class="p-3">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left pb-1.5 text-gray-500 font-medium">Denom</th>
                                    <th class="text-center pb-1.5 text-gray-400">×</th>
                                    <th class="text-center pb-1.5 text-gray-500 font-medium">Qty</th>
                                    <th class="text-center pb-1.5 text-gray-400">=</th>
                                    <th class="text-right pb-1.5 text-gray-500 font-medium">Amt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach(\App\Models\BoardTransaction::NLB_TIERS as $col => $denom)
                                <tr>
                                    <td class="py-1 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                    <td class="text-center text-gray-300">×</td>
                                    <td class="py-1">
                                        <input type="number" name="{{ $col }}" min="0"
                                               x-model.number="nlb['{{ $col }}']"
                                               @input="calcWinning"
                                               class="w-full text-center border border-gray-200 rounded px-1.5 py-0.5 text-xs
                                                      focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none"
                                               placeholder="0">
                                    </td>
                                    <td class="text-center text-gray-300">=</td>
                                    <td class="text-right font-medium text-gray-700"
                                        x-text="nlb['{{ $col }}'] ? fmt(nlb['{{ $col }}'] * {{ $denom }}) : ''">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-blue-200 bg-blue-50">
                                    <td colspan="4" class="py-1.5 pl-1 text-xs font-bold text-blue-800">NLB Total</td>
                                    <td class="py-1.5 pr-1 text-right text-xs font-bold text-blue-800"
                                        x-text="fmt(nlbTotal)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- DLB Winning --}}
                <div class="rounded-xl border border-purple-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-purple-600 px-4 py-2.5 flex justify-between items-center">
                        <span class="text-sm font-bold text-white">DLB — Winning</span>
                        <span class="text-purple-100 text-xs">Development Lottery Board</span>
                    </div>
                    <div class="p-3">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left pb-1.5 text-gray-500 font-medium">Denom</th>
                                    <th class="text-center pb-1.5 text-gray-400">×</th>
                                    <th class="text-center pb-1.5 text-gray-500 font-medium">Qty</th>
                                    <th class="text-center pb-1.5 text-gray-400">=</th>
                                    <th class="text-right pb-1.5 text-gray-500 font-medium">Amt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach(\App\Models\BoardTransaction::DLB_TIERS as $col => $denom)
                                <tr>
                                    <td class="py-1 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                    <td class="text-center text-gray-300">×</td>
                                    <td class="py-1">
                                        <input type="number" name="{{ $col }}" min="0"
                                               x-model.number="dlb['{{ $col }}']"
                                               @input="calcWinning"
                                               class="w-full text-center border border-gray-200 rounded px-1.5 py-0.5 text-xs
                                                      focus:ring-1 focus:ring-purple-400 focus:border-purple-400 outline-none"
                                               placeholder="0">
                                    </td>
                                    <td class="text-center text-gray-300">=</td>
                                    <td class="text-right font-medium text-gray-700"
                                        x-text="dlb['{{ $col }}'] ? fmt(dlb['{{ $col }}'] * {{ $denom }}) : ''">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-purple-200 bg-purple-50">
                                    <td colspan="4" class="py-1.5 pl-1 text-xs font-bold text-purple-800">DLB Total</td>
                                    <td class="py-1.5 pr-1 text-right text-xs font-bold text-purple-800"
                                        x-text="fmt(dlbTotal)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Grand winning badge --}}
                <div class="rounded-xl border-2 border-indigo-300 bg-indigo-50 px-4 py-3 flex justify-between items-center">
                    <span class="text-sm font-bold text-indigo-800">Grand Total Winning</span>
                    <span class="text-xl font-extrabold text-indigo-700" x-text="fmt(grandWinning)">0</span>
                </div>
            </div>

            {{-- RIGHT: Cash Counter + Payment Summary --}}
            <div class="space-y-4">

                {{-- Cash Counter --}}
                <div class="rounded-xl border border-green-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-green-600 px-4 py-2.5 flex justify-between items-center">
                        <span class="text-sm font-bold text-white">Cash Counter</span>
                        <span class="text-green-100 text-xs">Physical denominations</span>
                    </div>
                    <div class="p-3">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left pb-1.5 text-gray-500 font-medium">Denom</th>
                                    <th class="text-center pb-1.5 text-gray-400">×</th>
                                    <th class="text-center pb-1.5 text-gray-500 font-medium">Qty</th>
                                    <th class="text-center pb-1.5 text-gray-400">=</th>
                                    <th class="text-right pb-1.5 text-gray-500 font-medium">Amt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach(\App\Models\BoardTransaction::CASH_DENOMS as $col => $denom)
                                <tr>
                                    <td class="py-1 text-gray-700 font-medium">{{ number_format($denom) }}</td>
                                    <td class="text-center text-gray-300">×</td>
                                    <td class="py-1">
                                        <input type="number" name="{{ $col }}" min="0"
                                               x-model.number="cashQty['{{ $col }}']"
                                               @input="calcCash"
                                               class="w-full text-center border border-gray-200 rounded px-1.5 py-0.5 text-xs
                                                      focus:ring-1 focus:ring-green-400 focus:border-green-400 outline-none"
                                               placeholder="0">
                                    </td>
                                    <td class="text-center text-gray-300">=</td>
                                    <td class="text-right font-medium text-gray-700"
                                        x-text="cashQty['{{ $col }}'] ? fmt(cashQty['{{ $col }}'] * {{ $denom }}) : ''">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-green-200 bg-green-50">
                                    <td colspan="4" class="py-1.5 pl-1 text-xs font-bold text-green-800">Cash Total</td>
                                    <td class="py-1.5 pr-1 text-right text-xs font-bold text-green-800"
                                        x-text="fmt(cashTotal)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Bank Deposits --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Bank Deposits (Rs.)</label>
                    <input type="number" name="bank_deposits" min="0" step="0.01"
                           x-model.number="bankDeposits"
                           @input="calcTotal"
                           class="erp-input w-full text-lg font-semibold" placeholder="0.00">
                </div>

                {{-- Payment summary --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-gray-800 px-4 py-2.5">
                        <span class="text-sm font-bold text-white">Payment Summary</span>
                    </div>
                    <div class="p-4 space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">NLB Winning</span>
                            <span class="font-medium text-blue-700" x-text="fmt(nlbTotal)">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">DLB Winning</span>
                            <span class="font-medium text-purple-700" x-text="fmt(dlbTotal)">0</span>
                        </div>
                        <div class="flex justify-between text-sm border-t pt-2.5">
                            <span class="font-semibold text-gray-700">Total Winning</span>
                            <span class="font-bold text-indigo-700" x-text="fmt(grandWinning)">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Cash Paid</span>
                            <span class="font-medium text-green-700" x-text="fmt(cashTotal)">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Bank Deposits</span>
                            <span class="font-medium text-gray-700" x-text="fmt(bankDeposits)">0</span>
                        </div>
                        <div class="flex justify-between text-base border-t-2 pt-3">
                            <span class="font-extrabold text-gray-900">Total Payment</span>
                            <span class="font-extrabold text-gray-900" x-text="fmt(totalPayment)">0</span>
                        </div>
                        <p class="text-xs text-gray-400 text-right">
                            (Winning + Cash + Bank)
                        </p>
                    </div>
                </div>

                {{-- Grand total box --}}
                <div class="rounded-xl border-2 border-gray-800 bg-gray-900 px-5 py-4 text-center">
                    <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Will reduce balance by</p>
                    <p class="text-3xl font-extrabold text-white" x-text="fmt(totalPayment)">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         CREDIT section
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="type === 'credit'"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0">

        <div class="rounded-xl border border-amber-200 bg-white shadow-sm p-5">
            <h3 class="text-sm font-semibold text-amber-700 uppercase tracking-wide mb-4">Credit Entry</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Credit Amount (Rs.)</label>
                    <input type="number" name="credit_amount" min="0" step="0.01"
                           x-model.number="creditAmount"
                           value="{{ old('credit_amount') }}"
                           class="erp-input w-full text-lg font-bold text-amber-700" placeholder="0.00">
                </div>
                <div class="flex items-end">
                    <div class="w-full rounded-lg bg-amber-50 border border-amber-200 p-3 text-center">
                        <p class="text-xs text-amber-500 mb-1">Will add to balance</p>
                        <p class="text-xl font-extrabold text-amber-700"
                           x-text="fmt(creditAmount)">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Submit ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="flex-1 md:flex-none md:w-64 rounded-xl bg-blue-600 hover:bg-blue-700
                       text-white font-bold text-sm py-3 tracking-wide transition-colors shadow-sm">
            Save Transaction
        </button>
        <a href="{{ route('board-transactions.index') }}"
           class="rounded-xl border border-gray-300 text-gray-600 text-sm font-medium px-6 py-3 hover:bg-gray-50">
            Cancel
        </a>
    </div>

</form>
</div>{{-- end x-data --}}

@push('scripts')
<script>
function boardTxForm() {
    return {
        type: 'get_tickets',
        ticketValue:   0,
        creditAmount:  0,
        bankDeposits:  0,

        nlb: @json(array_fill_keys(array_keys(\App\Models\BoardTransaction::NLB_TIERS), 0)),
        dlb: @json(array_fill_keys(array_keys(\App\Models\BoardTransaction::DLB_TIERS), 0)),
        cashQty: @json(array_fill_keys(array_keys(\App\Models\BoardTransaction::CASH_DENOMS), 0)),

        nlbTotal:     0,
        dlbTotal:     0,
        grandWinning: 0,
        cashTotal:    0,
        totalPayment: 0,

        init() {
            this.$watch('bankDeposits', () => this.calcTotal());
        },

        calcWinning() {
            const nlbTiers = @json(\App\Models\BoardTransaction::NLB_TIERS);
            const dlbTiers = @json(\App\Models\BoardTransaction::DLB_TIERS);

            this.nlbTotal = Object.entries(nlbTiers)
                .reduce((s, [col, denom]) => s + (parseInt(this.nlb[col]) || 0) * denom, 0);

            this.dlbTotal = Object.entries(dlbTiers)
                .reduce((s, [col, denom]) => s + (parseInt(this.dlb[col]) || 0) * denom, 0);

            this.grandWinning = this.nlbTotal + this.dlbTotal;
            this.calcTotal();
        },

        calcCash() {
            const cashDenoms = @json(\App\Models\BoardTransaction::CASH_DENOMS);
            this.cashTotal = Object.entries(cashDenoms)
                .reduce((s, [col, denom]) => s + (parseInt(this.cashQty[col]) || 0) * denom, 0);
            this.calcTotal();
        },

        calcTotal() {
            this.totalPayment = this.grandWinning
                              + this.cashTotal
                              + (parseFloat(this.bankDeposits) || 0);
        },

        // Write computed totals into hidden inputs so PHP controller
        // has winning_amount and cash_amount as fallback.
        prepareSubmit() {
            this.$refs.h_winning.value = this.grandWinning;
            this.$refs.h_cash.value    = this.cashTotal;
        },

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
