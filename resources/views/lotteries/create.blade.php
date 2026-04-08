<x-layouts.app title="Add Lottery">

<div class="max-w-lg">
    <div class="mb-5">
        <a href="{{ route('lotteries.index') }}"
           class="text-sm text-blue-600 hover:underline">← Back to Lotteries</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Add New Lottery</h2>

        <form method="POST" action="{{ route('lotteries.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lottery Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="e.g. Mahajana Sampatha"
                       class="erp-input w-full @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Board <span class="text-red-500">*</span></label>
                <select name="board" required
                        class="erp-input w-full @error('board') border-red-400 @enderror">
                    <option value="">— Select board —</option>
                    <option value="NLB" {{ old('board') === 'NLB' ? 'selected' : '' }}>NLB (National Lottery Board)</option>
                    <option value="DLB" {{ old('board') === 'DLB' ? 'selected' : '' }}>DLB (Development Lottery Board)</option>
                </select>
                @error('board')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (Rs.) <span class="text-red-500">*</span></label>
                <input type="number" name="unit_price" value="{{ old('unit_price') }}" required
                       min="0" step="0.01" placeholder="e.g. 40.00"
                       class="erp-input w-full @error('unit_price') border-red-400 @enderror">
                @error('unit_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order') }}"
                       min="1" step="1" placeholder="e.g. 1"
                       class="erp-input w-full @error('sort_order') border-red-400 @enderror">
                <p class="mt-1 text-xs text-gray-400">Controls column position in Ticket Distribution & Sales tables. Leave blank to use creation order.</p>
                @error('sort_order')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Lottery
                </button>
                <a href="{{ route('lotteries.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
