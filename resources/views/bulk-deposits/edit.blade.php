<x-layouts.app title="Edit Bulk Deposit">

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
        <a href="{{ route('bulk-deposits.index') }}"
           class="hover:text-indigo-500 transition-colors">Bulk Deposits</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-700 dark:text-slate-300">Edit #{{ $bulkDeposit->id }}</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Bulk Deposit</h1>
    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
        Only <span class="font-semibold text-yellow-600 dark:text-yellow-400">Pending</span>
        records can be edited. Changes here do not affect any distributed daily records.
    </p>
</div>

{{-- Status indicator --}}
<div class="mb-5 flex items-center gap-2.5 rounded-xl border border-yellow-200 dark:border-yellow-800/50
            bg-yellow-50 dark:bg-yellow-900/10 px-4 py-3 text-xs text-yellow-800 dark:text-yellow-300">
    <span class="h-2 w-2 rounded-full bg-yellow-500 dark:bg-yellow-400"></span>
    <span><strong>Status: Pending</strong> — This record has not been distributed yet.</span>
    <span class="ml-auto text-yellow-600 dark:text-yellow-400 font-medium">
        #{{ $bulkDeposit->id }} · Created {{ $bulkDeposit->created_at->format('d M Y') }}
    </span>
</div>

<div class="max-w-2xl">
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/5 p-6">
        @include('bulk-deposits._form', [
            'assistants'  => $assistants,
            'deposit'     => $bulkDeposit,
            'formAction'  => route('bulk-deposits.update', $bulkDeposit),
            'formMethod'  => 'PUT',
            'submitLabel' => 'Save Changes',
        ])
    </div>
</div>

</x-layouts.app>
