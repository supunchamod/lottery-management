<x-layouts.app title="Add Cheque">

<div class="max-w-lg mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('cheques.index') }}"
           class="rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 p-2 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Add Cheque</h2>
            <p class="text-sm text-gray-500 mt-0.5">Record a new cheque for tracking</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6">
        <form method="POST" action="{{ route('cheques.store') }}">
            @csrf

            @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Bank Name *</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                           class="erp-input w-full" placeholder="e.g. People's Bank" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Cheque Number *</label>
                    <input type="text" name="cheque_no" value="{{ old('cheque_no') }}"
                           class="erp-input w-full font-mono" placeholder="e.g. 001234" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Amount (Rs.) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}"
                           min="0" step="0.01"
                           class="erp-input w-full" placeholder="0.00" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="erp-input w-full" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                    <select name="status" class="erp-input w-full">
                        <option value="pending"  {{ old('status', 'pending') === 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="cleared"  {{ old('status') === 'cleared'             ? 'selected' : '' }}>Cleared</option>
                    </select>
                </div>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-700 text-white
                               text-sm font-bold py-2.5 transition-colors">
                    Save Cheque
                </button>
                <a href="{{ route('cheques.index') }}"
                   class="rounded-xl border border-gray-300 text-gray-600 text-sm font-medium
                          px-5 py-2.5 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

</x-layouts.app>
