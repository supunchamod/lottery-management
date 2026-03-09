<x-layouts.app title="Expenses">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Expenses</h2>
            <p class="text-sm text-gray-500">Track and manage daily operational expenses.</p>
        </div>
        <button onclick="document.getElementById('expenseModal').classList.remove('hidden')"
                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Expense
        </button>
    </div>

    {{-- Stats --}}
    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">This Month</p>
            <p class="text-xl font-bold text-gray-900">Rs.{{ number_format($monthTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Today</p>
            <p class="text-xl font-bold text-gray-900">Rs.{{ number_format($todayTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Entries This Month</p>
            <p class="text-xl font-bold text-gray-900">{{ $monthCount ?? 0 }}</p>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($expenses ?? [] as $e)
                        <tr>
                            <td class="px-5 py-3 text-gray-600">{{ $e->date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $e->title }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-rose-600">Rs.{{ number_format($e->amount, 2) }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $e->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-sm text-gray-400">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Expense Modal --}}
    <div id="expenseModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl bg-white shadow-2xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Add Expense</h3>
                <button onclick="document.getElementById('expenseModal').classList.add('hidden')"
                        class="rounded-md p-1 hover:bg-gray-100 text-gray-400">✕</button>
            </div>
            <form method="POST" action="{{ route('expenses.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Date</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="erp-input" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Title</label>
                        <input type="text" name="title" class="erp-input" placeholder="e.g. Office Supplies" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (Rs.)</label>
                        <input type="number" name="amount" step="0.01" min="0" class="erp-input" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Description</label>
                        <textarea name="description" rows="2" class="erp-input resize-none" placeholder="Optional details..."></textarea>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="flex-1 rounded-lg bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save</button>
                    <button type="button" onclick="document.getElementById('expenseModal').classList.add('hidden')"
                            class="flex-1 rounded-lg border border-gray-200 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
