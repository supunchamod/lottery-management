<x-layouts.app title="Expenses">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Expenses</h2>
            <p class="text-sm text-gray-500">Track and manage daily operational expenses.</p>
        </div>
        <button onclick="openAddModal()"
                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Expense
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

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
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($expenses ?? [] as $e)
                        <tr>
                            <td class="px-5 py-3 text-gray-600">{{ $e->date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $e->title }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-rose-600">Rs.{{ number_format($e->amount, 2) }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $e->description }}</td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Edit --}}
                                    <button type="button"
                                            onclick="openEditModal(
                                                {{ $e->id }},
                                                '{{ $e->date->format('Y-m-d') }}',
                                                @js($e->title),
                                                '{{ number_format($e->amount, 2, '.', '') }}',
                                                @js($e->description ?? '')
                                            )"
                                            class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button"
                                            onclick="confirmDelete({{ $e->id }}, @js($e->title))"
                                            class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-sm text-gray-400">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(($expenses ?? collect())->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    {{-- ── Add Expense Modal ─────────────────────────────────────────────────── --}}
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

    {{-- ── Edit Expense Modal ────────────────────────────────────────────────── --}}
    <div id="editExpenseModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl bg-white shadow-2xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Edit Expense</h3>
                <button onclick="document.getElementById('editExpenseModal').classList.add('hidden')"
                        class="rounded-md p-1 hover:bg-gray-100 text-gray-400">✕</button>
            </div>
            <form id="editExpenseForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Date</label>
                        <input type="date" name="date" id="edit_date" class="erp-input" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Title</label>
                        <input type="text" name="title" id="edit_title" class="erp-input" placeholder="e.g. Office Supplies" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (Rs.)</label>
                        <input type="number" name="amount" id="edit_amount" step="0.01" min="0" class="erp-input" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Description</label>
                        <textarea name="description" id="edit_description" rows="2" class="erp-input resize-none" placeholder="Optional details..."></textarea>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="flex-1 rounded-lg bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">Update</button>
                    <button type="button" onclick="document.getElementById('editExpenseModal').classList.add('hidden')"
                            class="flex-1 rounded-lg border border-gray-200 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden delete form (submitted programmatically by SweetAlert confirm) --}}
    <form id="deleteExpenseForm" method="POST" action="" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openAddModal() {
            document.getElementById('expenseModal').classList.remove('hidden');
        }

        function openEditModal(id, date, title, amount, description) {
            const form = document.getElementById('editExpenseForm');
            form.action = '/expenses/' + id;
            document.getElementById('edit_date').value        = date;
            document.getElementById('edit_title').value       = title;
            document.getElementById('edit_amount').value      = amount;
            document.getElementById('edit_description').value = description;
            document.getElementById('editExpenseModal').classList.remove('hidden');
        }

        function confirmDelete(id, title) {
            Swal.fire({
                title: 'Delete Expense?',
                html: 'Are you sure you want to delete <strong>' + title + '</strong>?<br><small style="color:#94a3b8">This action cannot be undone.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                focusCancel: true,
            }).then(function(result) {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteExpenseForm');
                    form.action = '/expenses/' + id;
                    form.submit();
                }
            });
        }
    </script>

</x-layouts.app>
