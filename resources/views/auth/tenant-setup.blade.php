<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Welcome to {{ $tenant->pharmacy_name }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            Set up your administrator account to get started with your pharmacy management system.
        </p>
    </div>

    <!-- Tenant Information -->
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Access Code Verified</h3>
                <div class="mt-2 text-sm text-green-700">
                    <p><strong>Pharmacy:</strong> {{ $tenant->pharmacy_name }}</p>
                    <p><strong>Location:</strong> {{ $tenant->city }}, {{ $tenant->state }}</p>
                    <p><strong>Plan:</strong> {{ ucfirst($tenant->subscription_plan) }}</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tenant.setup.complete') }}">
        @csrf

        <!-- Admin Name -->
        <div class="mb-4">
            <x-input-label for="admin_name" :value="__('Administrator Name')" />
            <x-text-input id="admin_name" class="block mt-1 w-full" 
                          type="text" 
                          name="admin_name" 
                          :value="old('admin_name')" 
                          required 
                          autofocus 
                          autocomplete="name" />
            <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
        </div>

        <!-- Admin Email -->
        <div class="mb-4">
            <x-input-label for="admin_email" :value="__('Administrator Email')" />
            <x-text-input id="admin_email" class="block mt-1 w-full" 
                          type="email" 
                          name="admin_email" 
                          :value="old('admin_email')" 
                          required 
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-500">
                This will be your login email for the system.
            </p>
        </div>

        <!-- Admin Password -->
        <div class="mb-4">
            <x-input-label for="admin_password" :value="__('Password')" />
            <x-text-input id="admin_password" class="block mt-1 w-full"
                          type="password"
                          name="admin_password"
                          required 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <x-input-label for="admin_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="admin_password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="admin_password_confirmation" 
                          required 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('admin_password_confirmation')" class="mt-2" />
        </div>

        <!-- Security Notice -->
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Important Security Information</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Choose a strong password with at least 8 characters</li>
                            <li>This account will have full administrative access to your pharmacy system</li>
                            <li>You can create additional user accounts after setup is complete</li>
                            <li>Keep your login credentials secure and don't share them</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button>
                {{ __('Complete Setup & Login') }}
            </x-primary-button>
        </div>
    </form>

    <!-- What's Next Section -->
    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">What's Next?</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>After completing the setup, you'll be able to:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Access your pharmacy management dashboard</li>
                        <li>Set up your product inventory</li>
                        <li>Create additional user accounts for your staff</li>
                        <li>Configure your pharmacy settings and preferences</li>
                        <li>Start managing sales, prescriptions, and inventory</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
