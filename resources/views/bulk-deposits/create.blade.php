<x-layouts.app title="New Bulk Deposit">

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
        Capture a summary entry for a date range. Only one pending record per assistant is allowed.
    </p>
</div>

{{-- Pending-rule notice --}}
<div class="mb-5 rounded-xl border border-amber-200 dark:border-amber-800/50
            bg-amber-50 dark:bg-amber-900/10 px-4 py-3 text-xs text-amber-800 dark:text-amber-300">
    <strong>Rule:</strong> If the selected assistant already has a <em>Pending</em> bulk record,
    this form will be blocked. Distribute (or delete) the existing record first.
</div>

<div class="max-w-2xl">
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/5 p-6">
        @include('bulk-deposits._form', [
            'assistants'  => $assistants,
            'formAction'  => route('bulk-deposits.store'),
            'formMethod'  => 'POST',
            'submitLabel' => 'Create Bulk Entry',
        ])
    </div>
</div>

</x-layouts.app>
