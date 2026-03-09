<x-layouts.app title="Edit Lottery">

<div class="max-w-lg">
    <div class="mb-5">
        <a href="{{ route('lotteries.index') }}"
           class="text-sm text-blue-600 hover:underline">← Back to Lotteries</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Edit: {{ $lottery->name }}</h2>

        <form method="POST" action="{{ route('lotteries.update', $lottery) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lottery Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $lottery->name) }}" required
                       class="erp-input w-full @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Board <span class="text-red-500">*</span></label>
                <select name="board" required
                        class="erp-input w-full @error('board') border-red-400 @enderror">
                    <option value="NLB" {{ old('board', $lottery->board) === 'NLB' ? 'selected' : '' }}>NLB (National Lottery Board)</option>
                    <option value="DLB" {{ old('board', $lottery->board) === 'DLB' ? 'selected' : '' }}>DLB (Development Lottery Board)</option>
                </select>
                @error('board')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (Rs.) <span class="text-red-500">*</span></label>
                <input type="number" name="unit_price" value="{{ old('unit_price', $lottery->unit_price) }}" required
                       min="0" step="0.01"
                       class="erp-input w-full @error('unit_price') border-red-400 @enderror">
                @error('unit_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (%) <span class="text-red-500">*</span></label>
                <input type="number" name="commission_rate" value="{{ old('commission_rate', $lottery->commission_rate) }}" required
                       min="0" max="100" step="0.01"
                       class="erp-input w-full @error('commission_rate') border-red-400 @enderror">
                @error('commission_rate')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update
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
