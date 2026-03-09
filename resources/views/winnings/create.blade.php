<x-layouts.app title="Winning Entry">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Winning Ticket Entry</h2>
            <p class="text-sm text-gray-500">Enter today's winning counts for NLB and DLB. Totals are calculated live.</p>
        </div>
        <input type="date" id="winDate" value="{{ request('date', now()->toDateString()) }}"
               class="erp-input !w-auto"
               onchange="window.location.href='{{ route('winnings.index') }}?date='+this.value">
    </div>

    <form method="POST" action="{{ route('winnings.store') }}"
          x-data="winningForm()"
          x-init="recalc()">
        @csrf

        <input type="hidden" name="date" value="{{ request('date', now()->toDateString()) }}">

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- ════════════ NLB SECTION ════════════ --}}
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between bg-blue-600 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-white/20 px-2 py-0.5 text-xs font-bold text-white">NLB</span>
                        <h3 class="text-sm font-semibold text-white">National Lottery Board</h3>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-200">NLB Total</p>
                        <p class="text-lg font-bold text-white" x-text="'Rs.'+fmt(nlbTotal)"></p>
                    </div>
                </div>

                <div class="p-4">
                    {{-- Column headers --}}
                    <div class="mb-2 grid grid-cols-3 gap-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        <span>Denomination</span>
                        <span class="text-center">Qty</span>
                        <span class="text-right">Subtotal</span>
                    </div>

                    @php
                        $nlbTiers = [
                            'nlb_40'=>40, 'nlb_80'=>80, 'nlb_120'=>120, 'nlb_160'=>160,
                            'nlb_200'=>200, 'nlb_240'=>240, 'nlb_500'=>500, 'nlb_1000'=>1000,
                            'nlb_1080'=>1080, 'nlb_2000'=>2000, 'nlb_4000'=>4000,
                            'nlb_5000'=>5000, 'nlb_6000'=>6000, 'nlb_15000'=>15000,
                        ];
                    @endphp

                    <div class="space-y-1" x-ref="nlbRows">
                        @foreach($nlbTiers as $col => $denom)
                        <div class="grid grid-cols-3 items-center gap-2 rounded-lg px-2 py-1
                                    {{ $loop->even ? 'bg-blue-50/40' : 'bg-white' }}">
                            <label class="text-sm font-medium text-gray-700">
                                Rs.<strong>{{ number_format($denom) }}</strong>
                            </label>
                            <input type="number"
                                   name="{{ $col }}"
                                   min="0"
                                   placeholder="0"
                                   value="{{ old($col, $existing->{$col} ?? 0) }}"
                                   @input="updateNlb('{{ $col }}', $event.target.value, {{ $denom }})"
                                   class="erp-input text-center !py-1.5">
                            <p class="text-right text-sm font-semibold text-blue-600"
                               x-text="'Rs.'+fmt(nlb['{{ $col }}'] * {{ $denom }})"></p>
                        </div>
                        @endforeach
                    </div>

                    {{-- NLB Total --}}
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-blue-600 px-4 py-2.5">
                        <span class="text-sm font-bold text-white">NLB Total</span>
                        <span class="text-lg font-bold text-white" x-text="'Rs.'+fmt(nlbTotal)"></span>
                    </div>
                </div>
            </div>

            {{-- ════════════ DLB SECTION ════════════ --}}
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between bg-orange-500 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-white/20 px-2 py-0.5 text-xs font-bold text-white">DLB</span>
                        <h3 class="text-sm font-semibold text-white">Development Lottery Board</h3>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-orange-100">DLB Total</p>
                        <p class="text-lg font-bold text-white" x-text="'Rs.'+fmt(dlbTotal)"></p>
                    </div>
                </div>

                <div class="p-4">
                    <div class="mb-2 grid grid-cols-3 gap-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        <span>Denomination</span>
                        <span class="text-center">Qty</span>
                        <span class="text-right">Subtotal</span>
                    </div>

                    @php
                        $dlbTiers = [
                            'dlb_40'=>40, 'dlb_80'=>80, 'dlb_120'=>120, 'dlb_200'=>200,
                            'dlb_240'=>240, 'dlb_280'=>280, 'dlb_400'=>400, 'dlb_500'=>500,
                            'dlb_1000'=>1000, 'dlb_2000'=>2000, 'dlb_4000'=>4000,
                        ];
                    @endphp

                    <div class="space-y-1">
                        @foreach($dlbTiers as $col => $denom)
                        <div class="grid grid-cols-3 items-center gap-2 rounded-lg px-2 py-1
                                    {{ $loop->even ? 'bg-orange-50/40' : 'bg-white' }}">
                            <label class="text-sm font-medium text-gray-700">
                                Rs.<strong>{{ number_format($denom) }}</strong>
                            </label>
                            <input type="number"
                                   name="{{ $col }}"
                                   min="0"
                                   placeholder="0"
                                   value="{{ old($col, $existing->{$col} ?? 0) }}"
                                   @input="updateDlb('{{ $col }}', $event.target.value, {{ $denom }})"
                                   class="erp-input text-center !py-1.5">
                            <p class="text-right text-sm font-semibold text-orange-600"
                               x-text="'Rs.'+fmt(dlb['{{ $col }}'] * {{ $denom }})"></p>
                        </div>
                        @endforeach
                    </div>

                    {{-- DLB Total --}}
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-orange-500 px-4 py-2.5">
                        <span class="text-sm font-bold text-white">DLB Total</span>
                        <span class="text-lg font-bold text-white" x-text="'Rs.'+fmt(dlbTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Grand Total Bar + Save ───────────────────────────────────────── --}}
        <div class="mt-5 rounded-xl bg-slate-900 p-5 shadow-lg">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-center">
                <div class="text-center sm:border-r border-slate-700">
                    <p class="text-xs text-slate-400">NLB Total</p>
                    <p class="text-xl font-bold text-blue-400" x-text="'Rs.'+fmt(nlbTotal)"></p>
                </div>
                <div class="text-center sm:border-r border-slate-700">
                    <p class="text-xs text-slate-400">DLB Total</p>
                    <p class="text-xl font-bold text-orange-400" x-text="'Rs.'+fmt(dlbTotal)"></p>
                </div>
                <div class="text-center sm:border-r border-slate-700">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Grand Total</p>
                    <p class="text-2xl font-black text-white" x-text="'Rs.'+fmt(nlbTotal + dlbTotal)"></p>
                </div>
                <div class="flex flex-col gap-2">
                    <button type="submit"
                            class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white
                                   hover:bg-blue-500 active:scale-[0.98] transition-all">
                        Save Winning Record
                    </button>
                    <a href="{{ route('winnings.index') }}"
                       class="w-full rounded-lg border border-slate-600 px-5 py-2 text-center text-sm
                              font-medium text-slate-300 hover:bg-slate-800 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    function winningForm() {
        const nlbTiers = {!! json_encode(array_keys(\App\Models\Winning::NLB_TIERS)) !!};
        const dlbTiers = {!! json_encode(array_keys(\App\Models\Winning::DLB_TIERS)) !!};
        const nlbVals  = {!! json_encode(\App\Models\Winning::NLB_TIERS) !!};
        const dlbVals  = {!! json_encode(\App\Models\Winning::DLB_TIERS) !!};

        const existing = @json($existing ?? null);

        // Build initial qty maps from existing record or zeros
        const nlbInit = {};
        nlbTiers.forEach(k => { nlbInit[k] = existing ? (existing[k] || 0) : 0; });

        const dlbInit = {};
        dlbTiers.forEach(k => { dlbInit[k] = existing ? (existing[k] || 0) : 0; });

        return {
            nlb: { ...nlbInit },
            dlb: { ...dlbInit },
            nlbTotal: 0,
            dlbTotal: 0,

            recalc() {
                this.nlbTotal = nlbTiers.reduce((s, k) => s + (this.nlb[k] || 0) * nlbVals[k], 0);
                this.dlbTotal = dlbTiers.reduce((s, k) => s + (this.dlb[k] || 0) * dlbVals[k], 0);
            },

            updateNlb(col, val, denom) {
                this.nlb[col] = parseInt(val) || 0;
                this.recalc();
            },

            updateDlb(col, val, denom) {
                this.dlb[col] = parseInt(val) || 0;
                this.recalc();
            },

            fmt(n) {
                return Number(n || 0).toLocaleString('en-LK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            },
        };
    }
    </script>
    @endpush

</x-layouts.app>
