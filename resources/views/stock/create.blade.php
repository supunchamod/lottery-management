<x-layouts.app title="Issue Lottery Stock">

<div class="max-w-lg">
    <div class="mb-5">
        <a href="{{ route('stock.index') }}"
           class="text-sm text-blue-600 hover:underline">← Back to Stock</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Issue Lottery Stock</h2>

        <form method="POST" action="{{ route('stock.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required
                       class="erp-input w-full @error('date') border-red-400 @enderror">
                @error('date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sales Assistant <span class="text-red-500">*</span></label>
                <select name="agent_id" required
                        class="erp-input w-full @error('agent_id') border-red-400 @enderror">
                    <option value="">— Select assistant —</option>
                    @foreach($assistants as $a)
                        <option value="{{ $a->id }}" {{ old('agent_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
                @error('agent_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lottery <span class="text-red-500">*</span></label>
                <select name="lottery_id" required id="lottery_select"
                        class="erp-input w-full @error('lottery_id') border-red-400 @enderror">
                    <option value="">— Select lottery —</option>
                    @foreach($lotteries as $l)
                        <option value="{{ $l->id }}"
                                data-price="{{ $l->unit_price }}"
                                data-board="{{ $l->board }}"
                                {{ old('lottery_id') == $l->id ? 'selected' : '' }}>
                            {{ $l->name }} ({{ $l->board }} — Rs.{{ number_format($l->unit_price, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('lottery_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Issued <span class="text-red-500">*</span></label>
                <input type="number" name="qty_issued" value="{{ old('qty_issued') }}" required
                       min="1" step="1" id="qty_input"
                       placeholder="e.g. 100"
                       class="erp-input w-full @error('qty_issued') border-red-400 @enderror">
                @error('qty_issued')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Live total value preview --}}
            <div id="value_preview" class="hidden rounded-lg bg-blue-50 border border-blue-100 px-4 py-3">
                <p class="text-xs text-blue-500 font-medium">Estimated Total Value</p>
                <p class="text-lg font-bold text-blue-700" id="total_value">Rs. 0.00</p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Stock
                </button>
                <a href="{{ route('stock.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const lotterySelect = document.getElementById('lottery_select');
    const qtyInput      = document.getElementById('qty_input');
    const preview       = document.getElementById('value_preview');
    const totalEl       = document.getElementById('total_value');

    function updatePreview() {
        const opt   = lotterySelect.selectedOptions[0];
        const price = opt ? parseFloat(opt.dataset.price || 0) : 0;
        const qty   = parseInt(qtyInput.value || 0);

        if (price > 0 && qty > 0) {
            totalEl.textContent = 'Rs. ' + (price * qty).toLocaleString('en-US', {minimumFractionDigits: 2});
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    lotterySelect.addEventListener('change', updatePreview);
    qtyInput.addEventListener('input', updatePreview);
</script>

</x-layouts.app>
