<x-layouts.app title="Add User">

<div class="max-w-lg mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}"
           class="rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 p-2 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Add User</h2>
            <p class="text-sm text-gray-500 mt-0.5">Create a new Admin or Sub-admin account</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="erp-input w-full" placeholder="e.g. Kasun Perera" required>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="erp-input w-full" placeholder="kasun@example.com" required>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Role *</label>
                <select name="role" class="erp-input w-full" required>
                    <option value="sub-admin" {{ old('role', 'sub-admin') === 'sub-admin' ? 'selected' : '' }}>Sub-admin</option>
                    <option value="admin"     {{ old('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Password *</label>
                <input type="password" name="password"
                       class="erp-input w-full" placeholder="Min. 8 characters, letters & numbers" required>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm Password *</label>
                <input type="password" name="password_confirmation"
                       class="erp-input w-full" placeholder="Repeat password" required>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 transition-colors">
                    Create User
                </button>
                <a href="{{ route('users.index') }}"
                   class="rounded-xl border border-gray-300 text-gray-600 text-sm font-medium px-5 py-2.5 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
