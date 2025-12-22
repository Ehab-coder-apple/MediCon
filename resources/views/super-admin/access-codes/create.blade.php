<x-superadmin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Generate New Access Code') }}
            </h2>
            <a href="{{ route('super-admin.access-codes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Access Codes
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <!-- Form Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Access Code Details</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Generate a new access code for a tenant administrator to access their pharmacy system.
                    </p>
                </div>

                <form method="POST" action="{{ route('super-admin.access-codes.store') }}" class="p-6">
                    @csrf

                    <!-- Tenant Selection -->
                    <div class="mb-6">
                        <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Tenant <span class="text-red-500">*</span>
                        </label>
                        <select name="tenant_id" id="tenant_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('tenant_id') border-red-500 @enderror">
                            <option value="">Choose a tenant...</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }} ({{ $tenant->pharmacy_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('tenant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <input type="text" name="description" id="description" value="{{ old('description') }}"
                            placeholder="e.g., Initial admin access for setup"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('description') border-red-500 @enderror">
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Optional description to help identify the purpose of this access code.</p>
                    </div>

                    <!-- Max Uses -->
                    <div class="mb-6">
                        <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-2">
                            Maximum Uses <span class="text-red-500">*</span>
                        </label>
                        <select name="max_uses" id="max_uses" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('max_uses') border-red-500 @enderror">
                            <option value="1" {{ old('max_uses', 1) == 1 ? 'selected' : '' }}>1 use (Single use)</option>
                            <option value="3" {{ old('max_uses') == 3 ? 'selected' : '' }}>3 uses</option>
                            <option value="5" {{ old('max_uses') == 5 ? 'selected' : '' }}>5 uses</option>
                            <option value="10" {{ old('max_uses') == 10 ? 'selected' : '' }}>10 uses</option>
                        </select>
                        @error('max_uses')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">How many times this access code can be used before it becomes inactive.</p>
                    </div>

                    <!-- Access Type -->
                    <div class="mb-6">
                        <label for="access_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Access Type <span class="text-red-500">*</span>
                        </label>
                        <select name="access_type" id="access_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('access_type') border-red-500 @enderror">
                            <option value="admin_setup" {{ old('access_type', request('type', 'admin_setup')) == 'admin_setup' ? 'selected' : '' }}>Admin Setup (First Time)</option>
                            <option value="user_registration" {{ old('access_type', request('type')) == 'user_registration' ? 'selected' : '' }}>User Registration</option>
                        </select>
                        @error('access_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Admin Setup for first-time tenant admin creation, User Registration for additional users.</p>
                    </div>

                    <!-- Default Role -->
                    <div class="mb-6">
                        <label for="role_assignment" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role_assignment" id="role_assignment" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('role_assignment') border-red-500 @enderror">
                            <option value="admin" {{ old('role_assignment', 'admin') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pharmacist" {{ old('role_assignment') == 'pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                            <option value="sales_staff" {{ old('role_assignment') == 'sales_staff' ? 'selected' : '' }}>Sales Staff</option>
                        </select>
                        @error('role_assignment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Role that will be assigned to users who register with this access code.</p>
                    </div>

                    <!-- Expiration Date -->
                    <div class="mb-6">
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                            Expiration Date (Optional)
                        </label>
                        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                            min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('expires_at') border-red-500 @enderror">
                        @error('expires_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave empty for no expiration. The code will expire automatically after the maximum uses are reached.</p>
                    </div>

                    <!-- Security Notice -->
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Security Notice</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Access codes should be shared securely with tenant administrators only</li>
                                        <li>Each code is unique and cannot be regenerated once created</li>
                                        <li>Codes can be revoked at any time if compromised</li>
                                        <li>Monitor usage and revoke unused codes periodically</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('super-admin.access-codes.index') }}" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Generate Access Code
                        </button>
                    </div>
                </form>
            </div>

            <!-- Information Card -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">How Access Codes Work</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="mb-2">Access codes provide secure, temporary access for tenant administrators to set up their pharmacy systems:</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Generate a unique access code for the specific tenant</li>
                                <li>Share the code securely with the tenant administrator</li>
                                <li>The administrator uses the code to gain initial access to their system</li>
                                <li>Once used (or expired), the code becomes inactive</li>
                                <li>The administrator can then set up their own permanent access credentials</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>
