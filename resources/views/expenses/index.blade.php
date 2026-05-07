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
    @php $hasFilter = array_filter($filters ?? []); @endphp
    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">This Month</p>
            <p class="text-xl font-bold text-gray-900">Rs.{{ number_format($monthTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Today</p>
            <p class="text-xl font-bold text-gray-900">Rs.{{ number_format($todayTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4 {{ $hasFilter ? 'ring-2 ring-blue-100' : '' }}">
            @if($hasFilter)
                <p class="text-xs text-blue-500 font-medium">Filtered Entries</p>
                <p class="text-xl font-bold text-gray-900">{{ $filteredCount ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Rs.{{ number_format($filteredTotal ?? 0, 2) }}</p>
            @else
                <p class="text-xs text-gray-400">Entries This Month</p>
                <p class="text-xl font-bold text-gray-900">{{ $monthCount ?? 0 }}</p>
            @endif
        </div>
    </div>

    {{-- ── Filter Panel ──────────────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-xl bg-white border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('expenses.index') }}" id="filterForm">

            {{-- Quick preset toggles --}}
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium text-gray-400">Quick:</span>
                @foreach (['this_week' => 'This Week', 'this_month' => 'This Month'] as $presetKey => $presetLabel)
                    <a href="{{ route('expenses.index') }}?preset={{ $presetKey }}"
                       class="inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-medium transition-colors
                              {{ ($filters['preset'] ?? '') === $presetKey
                                 ? 'bg-blue-600 text-white border-blue-600'
                                 : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                        {{ $presetLabel }}
                    </a>
                @endforeach
                @if(!empty($filters['preset']) || array_filter(array_diff_key($filters ?? [], ['preset' => ''])))
                    <a href="{{ route('expenses.index') }}"
                       class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-0.5 text-xs text-gray-400 hover:bg-gray-50 transition-colors">
                        All Time
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

                {{-- Search --}}
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Search by description or category…"
                               class="erp-input pl-8 text-sm">
                    </div>
                </div>

                {{-- Date From --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input text-sm">
                </div>

                {{-- Date To --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input text-sm">
                </div>

                {{-- Category --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Category</label>
                    <select name="category_id" class="erp-input text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Amount Range --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Amount Range (Rs.)</label>
                    <div class="flex items-center gap-1">
                        <input type="number" name="amount_min" value="{{ $filters['amount_min'] ?? '' }}"
                               placeholder="Min" min="0" step="0.01"
                               class="erp-input text-sm w-1/2">
                        <span class="text-gray-400 text-xs">–</span>
                        <input type="number" name="amount_max" value="{{ $filters['amount_max'] ?? '' }}"
                               placeholder="Max" min="0" step="0.01"
                               class="erp-input text-sm w-1/2">
                    </div>
                </div>

            </div>

            <div class="mt-3 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Apply Filters
                </button>
                @if(array_filter($filters ?? []))
                    <a href="{{ route('expenses.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                @endif

                {{-- Export Excel --}}
                <a href="{{ route('expenses.export') }}?{{ http_build_query(array_filter($filters ?? [])) }}"
                   class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </form>
    </div>

    {{-- ── Category Summary ─────────────────────────────────────────────────── --}}
    @if(($categorySummary ?? collect())->isNotEmpty())
    <div class="mb-4 rounded-xl bg-white border border-gray-100 shadow-sm">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Category Summary</h3>
                @php
                    $periodLabel = '';
                    $f = $filters ?? [];
                    if (!empty($f['date_from']) && !empty($f['date_to'])) {
                        $periodLabel = \Carbon\Carbon::parse($f['date_from'])->format('d M Y')
                                     . ' – '
                                     . \Carbon\Carbon::parse($f['date_to'])->format('d M Y');
                    } elseif (!empty($f['date_from'])) {
                        $periodLabel = 'From ' . \Carbon\Carbon::parse($f['date_from'])->format('d M Y');
                    } elseif (!empty($f['date_to'])) {
                        $periodLabel = 'Up to ' . \Carbon\Carbon::parse($f['date_to'])->format('d M Y');
                    } else {
                        $periodLabel = 'All Time';
                    }
                @endphp
                <p class="text-xs text-gray-400">{{ $periodLabel }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">Period Total</p>
                <p class="text-base font-bold text-gray-900">Rs.{{ number_format($filteredTotal ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Summary table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-5 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Entries</th>
                        <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total (Rs.)</th>
                        <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 w-32">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($categorySummary as $row)
                        @php
                            $share = ($filteredTotal ?? 0) > 0
                                ? ($row->total_amount / $filteredTotal) * 100
                                : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/40">
                            <td class="px-5 py-2.5">
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 border border-indigo-100">
                                    {{ $row->category?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-center text-gray-500">{{ $row->entry_count }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-rose-600">
                                Rs.{{ number_format($row->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-2.5">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-indigo-400"
                                             style="width: {{ number_format($share, 1) }}%"></div>
                                    </div>
                                    <span class="w-10 text-right text-xs text-gray-500">
                                        {{ number_format($share, 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-gray-700">Total</td>
                        <td class="px-5 py-2.5 text-center text-xs font-bold text-gray-700">
                            {{ ($categorySummary ?? collect())->sum('entry_count') }}
                        </td>
                        <td class="px-5 py-2.5 text-right text-xs font-bold text-rose-600">
                            Rs.{{ number_format($filteredTotal ?? 0, 2) }}
                        </td>
                        <td class="px-5 py-2.5 text-right text-xs text-gray-400">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Expenses Table ────────────────────────────────────────────────────── --}}
    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($expenses ?? [] as $e)
                        <tr>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $e->date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if($e->category)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 border border-indigo-100">
                                        {{ $e->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-rose-600 whitespace-nowrap">Rs.{{ number_format($e->amount, 2) }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $e->description }}</td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Edit --}}
                                    <button type="button"
                                            onclick="openEditModal(
                                                {{ $e->id }},
                                                '{{ $e->date->format('Y-m-d') }}',
                                                '{{ number_format($e->amount, 2, '.', '') }}',
                                                @js($e->description ?? ''),
                                                {{ $e->category_id ?? 'null' }}
                                            )"
                                            class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button"
                                            onclick="confirmDelete({{ $e->id }}, @js($e->category?->name ?? 'this expense'))"
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
                        <tr><td colspan="5" class="py-12 text-center text-sm text-gray-400">No expenses found.</td></tr>
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
                        <label class="mb-1 block text-xs font-medium text-gray-600">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" class="erp-input" required>
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (Rs.) <span class="text-rose-500">*</span></label>
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
                        <label class="mb-1 block text-xs font-medium text-gray-600">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" id="edit_category_id" class="erp-input" required>
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (Rs.) <span class="text-rose-500">*</span></label>
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

        function openEditModal(id, date, amount, description, categoryId) {
            const form = document.getElementById('editExpenseForm');
            form.action = '/expenses/' + id;
            document.getElementById('edit_date').value        = date;
            document.getElementById('edit_amount').value      = amount;
            document.getElementById('edit_description').value = description;

            const catSelect = document.getElementById('edit_category_id');
            catSelect.value = categoryId ?? '';

            document.getElementById('editExpenseModal').classList.remove('hidden');
        }

        function confirmDelete(id, label) {
            Swal.fire({
                title: 'Delete Expense?',
                html: 'Are you sure you want to delete <strong>' + label + '</strong>?<br><small style="color:#94a3b8">This action cannot be undone.</small>',
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
