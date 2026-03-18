<x-layouts.app title="Edit Assistant">

<div class="max-w-lg">
    <div class="mb-5">
        <a href="{{ route('assistants.index') }}"
           class="text-sm text-blue-600 hover:underline">← Back to Assistants</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Edit: {{ $assistant->name }}</h2>

        <form method="POST" action="{{ route('assistants.update', $assistant) }}" class="space-y-4">
            
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $assistant->name) }}" required
                       class="erp-input w-full @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $assistant->phone) }}" required
                       class="erp-input w-full @error('phone') border-red-400 @enderror">
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="2" required
                          class="erp-input w-full @error('address') border-red-400 @enderror">{{ old('address', $assistant->address) }}</textarea>
                @error('address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                <select name="route_id" class="erp-input w-full @error('route_id') border-red-400 @enderror">
                    <option value="">— Unassigned —</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ old('route_id', $assistant->route_id) == $route->id ? 'selected' : '' }}>
                            {{ $route->name }}
                        </option>
                    @endforeach
                </select>
                @error('route_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
                <p class="text-xs text-gray-500">Current Balance</p>
                <p class="text-lg font-bold {{ $assistant->current_balance > 0 ? 'text-red-600' : ($assistant->current_balance < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                    Rs. {{ number_format(abs($assistant->current_balance), 2) }}
                    @if($assistant->current_balance > 0) <span class="text-xs font-normal text-red-400">(Credit)</span>
                    @elseif($assistant->current_balance < 0) <span class="text-xs font-normal text-emerald-400">(Excess)</span>
                    @endif
                </p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update
                </button>
                <a href="{{ route('assistants.ledger', $assistant) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    View Ledger
                </a>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
