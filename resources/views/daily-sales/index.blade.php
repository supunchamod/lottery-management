<x-layouts.app title="Daily Sales">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Daily Sales Entry</h2>
            <p class="text-sm text-gray-500">Record ticket issuances, returns and cash collections per assistant.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" id="filterDate" value="{{ request('date', now()->toDateString()) }}"
                   class="erp-input !w-auto"
                   onchange="window.location.href='{{ route('daily-sales.index') }}?date='+this.value">
        </div>
    </div>

    {{-- ── Day totals banner ───────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-6">
        @php
            $bannerItems = [
                ['label'=>'Cash',     'value'=>$dayTotals['cash']    ?? 0, 'color'=>'text-emerald-700 bg-emerald-50'],
                ['label'=>'Winnings', 'value'=>$dayTotals['winning'] ?? 0, 'color'=>'text-violet-700 bg-violet-50'],
                ['label'=>'C+W',      'value'=>($dayTotals['cash'] ?? 0) + ($dayTotals['winning'] ?? 0), 'color'=>'text-blue-700 bg-blue-50'],
                ['label'=>'Issued',   'value'=>$dayTotals['issued']  ?? 0, 'color'=>'text-gray-700 bg-gray-50'],
                ['label'=>'Returns',  'value'=>$dayTotals['returns'] ?? 0, 'color'=>'text-orange-700 bg-orange-50'],
                ['label'=>'Balance',  'value'=>$dayTotals['balance'] ?? 0, 'color'=>'text-red-700 bg-red-50'],
            ];
        @endphp
        @foreach($bannerItems as $bi)
            <div class="rounded-lg {{ $bi['color'] }} border border-current/10 px-4 py-2.5">
                <p class="text-xs font-medium opacity-70">{{ $bi['label'] }}</p>
                <p class="text-base font-bold">Rs.{{ number_format($bi['value'], 2) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- ── Entry Form (Alpine.js) ──────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-6"
                 x-data="salesForm()"
                 x-init="init()">

                <h3 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wide">New Entry</h3>

                <form method="POST" action="{{ route('daily-records.store') }}" @submit="prepareSave">
                    @csrf

                    {{-- Date --}}
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Date</label>
                        <input type="date" name="date" x-model="form.date" class="erp-input" required>
                    </div>

                    {{-- Assistant Select --}}
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Sales Assistant</label>
                        <select name="assistant_id" x-model="form.assistant_id" class="erp-input" required>
                            <option value="">— Select Assistant —</option>
                            @foreach($assistants as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tickets Issued Value --}}
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">
                            Tickets Issued Value (Rs.)
                        </label>
                        <input type="number" name="tickets_issued_val" x-model.number="form.issuedVal"
                               @input="calc()" step="0.01" min="0" class="erp-input" placeholder="0.00" required>
                    </div>

                    {{-- Returns --}}
                    <div class="mb-4 grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Returns Qty</label>
                            <input type="number" name="returns_qty" x-model.number="form.returnsQty"
                                   min="0" class="erp-input" placeholder="0">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Returns Value (Rs.)</label>
                            <input type="number" name="returns_val" x-model.number="form.returnsVal"
                                   @input="calc()" step="0.01" min="0" class="erp-input" placeholder="0.00">
                        </div>
                    </div>

                    {{-- Winnings --}}
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">
                            Winnings Paid Out (Rs.)
                        </label>
                        <input type="number" name="winning_val" x-model.number="form.winningVal"
                               @input="calc()" step="0.01" min="0" class="erp-input" placeholder="0.00">
                    </div>

                    {{-- Cash Collected --}}
                    <div class="mb-5">
                        <label class="mb-1 block text-xs font-medium text-gray-600">
                            Cash Collected (Rs.)
                        </label>
                        <input type="number" name="cash_collected" x-model.number="form.cash"
                               @input="calc()" step="0.01" min="0" class="erp-input" placeholder="0.00">
                    </div>

                    {{-- Live Balance Box ──────────────────────────────────── --}}
                    <div class="mb-5 rounded-lg border-2 p-4 transition-colors"
                         :class="balance > 0 ? 'border-red-200 bg-red-50' :
                                 balance < 0 ? 'border-emerald-200 bg-emerald-50' :
                                               'border-gray-200 bg-gray-50'">

                        <p class="text-xs font-medium text-gray-500 mb-2">Balance Preview</p>
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500 space-y-0.5">
                                <p>Issued: <span class="font-medium text-gray-700" x-text="'Rs.'+fmt(form.issuedVal)"></span></p>
                                <p>− Returns: <span class="font-medium text-gray-700" x-text="'Rs.'+fmt(form.returnsVal)"></span></p>
                                <p>− Winnings: <span class="font-medium text-gray-700" x-text="'Rs.'+fmt(form.winningVal)"></span></p>
                                <p>− Cash: <span class="font-medium text-gray-700" x-text="'Rs.'+fmt(form.cash)"></span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold"
                                   :class="balance > 0 ? 'text-red-600' : balance < 0 ? 'text-emerald-600' : 'text-gray-500'"
                                   x-text="'Rs.'+fmt(Math.abs(balance))"></p>
                                <p class="text-xs font-medium mt-0.5"
                                   :class="balance > 0 ? 'text-red-500' : balance < 0 ? 'text-emerald-500' : 'text-gray-400'"
                                   x-text="balance > 0 ? '▲ Assistant Owes' : balance < 0 ? '▼ Agency Owes' : 'Settled'"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white
                                   hover:bg-blue-700 active:scale-[0.98] transition-all">
                        Save Record
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Sales Table ─────────────────────────────────────────────────── --}}
        <div class="lg:col-span-3">
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Records for {{ \Carbon\Carbon::parse(request('date', now()))->format('d M Y') }}
                    </h3>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-600">
                        {{ $sales->count() }} records
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70">
                                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Assistant</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">Value</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">Returns</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">Winning</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">Cash</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">C+W</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wide">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($sales as $s)
                                @php $cw = $s->cash_collected + $s->winning_val; @endphp
                                <tr>
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-gray-800">{{ $s->assistant->name }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-700">
                                        {{ number_format($s->tickets_issued_val, 0) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-orange-600">
                                        {{ number_format($s->returns_val, 0) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-violet-600">
                                        {{ number_format($s->winning_val, 0) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-emerald-600">
                                        {{ number_format($s->cash_collected, 0) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-medium text-blue-600">
                                        {{ number_format($cw, 0) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <span class="inline-block rounded px-2 py-0.5
                                                     {{ $s->balance > 0 ? 'balance-owes' : ($s->balance < 0 ? 'balance-credit' : 'balance-settled') }}">
                                            {{ number_format($s->balance, 0) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">
                                        No records for this date.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- Totals row --}}
                        @if($sales->count())
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 bg-blue-50/50 font-semibold text-xs">
                                <td class="px-4 py-3 text-blue-700">TOTALS</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($sales->sum('tickets_issued_val'), 0) }}</td>
                                <td class="px-4 py-3 text-right text-orange-600">{{ number_format($sales->sum('returns_val'), 0) }}</td>
                                <td class="px-4 py-3 text-right text-violet-600">{{ number_format($sales->sum('winning_val'), 0) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600">{{ number_format($sales->sum('cash_collected'), 0) }}</td>
                                <td class="px-4 py-3 text-right text-blue-600">{{ number_format($sales->sum('cash_collected') + $sales->sum('winning_val'), 0) }}</td>
                                <td class="px-4 py-3 text-right">
                                    @php $totalBal = $sales->sum('balance'); @endphp
                                    <span class="inline-block rounded px-2 py-0.5
                                                 {{ $totalBal > 0 ? 'balance-owes' : ($totalBal < 0 ? 'balance-credit' : 'balance-settled') }}">
                                        {{ number_format($totalBal, 0) }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function salesForm() {
        return {
            form: {
                date:        '{{ now()->toDateString() }}',
                assistant_id: '',
                issuedVal:   0,
                returnsQty:  0,
                returnsVal:  0,
                winningVal:  0,
                cash:        0,
            },
            balance: 0,

            init() { this.calc(); },

            calc() {
                this.balance = this.form.issuedVal
                    - (this.form.returnsVal + this.form.winningVal + this.form.cash);
            },

            fmt(n) {
                return Number(n || 0).toLocaleString('en-LK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            },

            prepareSave(e) {
                // Balance is computed server-side; just let the form submit.
            },
        };
    }
    </script>
    @endpush

</x-layouts.app>
