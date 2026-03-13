<x-layouts.app title="New Bulk Deposit">

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════════ --}}
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
        <a href="{{ route('bulk-deposits.index') }}"
           class="hover:text-indigo-500 transition-colors">Bulk Deposits</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-700 dark:text-slate-300">New Entry</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New Bulk Deposit</h1>
    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
        Create a multi-day sales buffer for a single assistant. Only one pending record is allowed per assistant.
    </p>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     FORM CARD
═══════════════════════════════════════════════════════════════════════ --}}
<div class="max-w-lg">
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/5 p-6">

        <form method="POST" action="{{ route('bulk-deposits.store') }}" class="space-y-5">
            @csrf

            {{-- Assistant --}}
            <div>
                <label for="assistant_id"
                       class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Sales Assistant <span class="text-red-500">*</span>
                </label>
                <select id="assistant_id" name="assistant_id"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                               bg-white dark:bg-slate-700/50
                               text-slate-900 dark:text-white
                               px-3.5 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50
                               @error('assistant_id') border-red-400 dark:border-red-500 @enderror">
                    <option value="">— Select assistant —</option>
                    @foreach($assistants as $a)
                    <option value="{{ $a->id }}" {{ old('assistant_id') == $a->id ? 'selected' : '' }}>
                        {{ $a->name }}
                    </option>
                    @endforeach
                </select>
                @error('assistant_id')
                <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400 font-medium">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Date Range --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date_from"
                           class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        From Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date_from" name="date_from"
                           value="{{ old('date_from', today()->toDateString()) }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50
                                  text-slate-900 dark:text-white
                                  px-3.5 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50
                                  @error('date_from') border-red-400 dark:border-red-500 @enderror">
                    @error('date_from')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_to"
                           class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        To Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date_to" name="date_to"
                           value="{{ old('date_to', today()->toDateString()) }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-700/50
                                  text-slate-900 dark:text-white
                                  px-3.5 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50
                                  @error('date_to') border-red-400 dark:border-red-500 @enderror">
                    @error('date_to')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes"
                       class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Notes <span class="text-slate-400 text-xs font-normal">(optional)</span>
                </label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Any remarks about this bulk entry…"
                          class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                 bg-white dark:bg-slate-700/50
                                 text-slate-900 dark:text-white
                                 px-3.5 py-2.5 text-sm resize-none
                                 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500/50">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info note --}}
            <div class="rounded-xl border border-amber-200 dark:border-amber-800/50
                        bg-amber-50 dark:bg-amber-900/10 px-4 py-3 text-xs text-amber-800 dark:text-amber-300">
                <strong>Note:</strong> Only one pending record is allowed per assistant.
                You must distribute (or the system must process) any existing pending record
                before creating a new one for the same assistant.
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-1">
                <a href="{{ route('bulk-deposits.index') }}"
                   class="flex-1 rounded-xl border border-slate-300 dark:border-slate-600
                          bg-white dark:bg-slate-800 py-2.5 text-center text-sm font-medium
                          text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-700
                               py-2.5 text-sm font-semibold text-white transition-colors">
                    Create Bulk Entry
                </button>
            </div>
        </form>

    </div>
</div>

</x-layouts.app>
