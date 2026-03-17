<x-layouts.app title="Add Scam Ticket">

<div class="max-w-xl mx-auto">

    {{-- Back link --}}
    <div class="mb-4 print:hidden">
        <a href="{{ route('scam-winnings.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Scam Records
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                bg-white dark:bg-slate-800/60 shadow-sm p-6"
         x-data="scamForm()">

        <h2 class="text-base font-bold text-slate-800 dark:text-white mb-5">Record a Scam Ticket</h2>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 px-4 py-3">
            <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('scam-winnings.store') }}" class="space-y-4">
            @csrf

            {{-- Sales Assistant --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                    Sales Assistant <span class="text-red-500">*</span>
                </label>
                <select name="assistant_id" required
                        @change="assistantId = $event.target.value"
                        class="erp-input w-full text-sm">
                    <option value="">— Select Assistant —</option>
                    @foreach($assistants as $a)
                        <option value="{{ $a->id }}" {{ old('assistant_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date" required
                       value="{{ old('date', today()->toDateString()) }}"
                       class="erp-input w-full text-sm">
            </div>

            {{-- Ticket Barcode --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                    Ticket Barcode / Serial Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="ticket_barcode" required
                       id="barcode-input"
                       value="{{ old('ticket_barcode') }}"
                       placeholder="Scan or type barcode…"
                       class="erp-input w-full text-sm font-mono"
                       autofocus>
                <p class="mt-1 text-xs text-slate-400">Use a barcode scanner or type manually.</p>
            </div>

            {{-- Reported vs Actual --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                        Reported Winning Value (Rs.) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="reported_winning_value" required min="0" step="0.01"
                           x-model.number="reported"
                           value="{{ old('reported_winning_value', 0) }}"
                           class="erp-input w-full text-sm text-center font-medium">
                    <p class="mt-1 text-xs text-slate-400">Amount the assistant claimed.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                        Actual Winning Value (Rs.) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="actual_winning_value" required min="0" step="0.01"
                           x-model.number="actual"
                           value="{{ old('actual_winning_value', 0) }}"
                           class="erp-input w-full text-sm text-center font-medium">
                    <p class="mt-1 text-xs text-slate-400">Real ticket value (0 = fully fake).</p>
                </div>
            </div>

            {{-- Auto-computed Difference --}}
            <div class="rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/40 px-4 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Difference (Amount to Pay Back)</span>
                    <span class="text-xl font-bold text-red-700 dark:text-red-400"
                          x-text="'Rs. ' + Math.max(0, reported - actual).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})">
                    </span>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Notes</label>
                <textarea name="notes" rows="2"
                          placeholder="Optional notes about the scam ticket…"
                          class="erp-input w-full text-sm resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-1">
                <a href="{{ route('scam-winnings.index') }}"
                   class="flex-1 inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600
                          bg-white dark:bg-slate-800 py-2.5 text-sm font-medium
                          text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-[2] inline-flex items-center justify-center gap-2 rounded-xl
                               bg-red-600 hover:bg-red-700 py-2.5 text-sm font-semibold text-white
                               shadow-md shadow-red-900/20 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Record Scam Ticket
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function scamForm() {
    return {
        reported:    {{ old('reported_winning_value', 0) }},
        actual:      {{ old('actual_winning_value', 0) }},
        assistantId: '{{ old('assistant_id', '') }}',
    };
}
</script>
@endpush

</x-layouts.app>
