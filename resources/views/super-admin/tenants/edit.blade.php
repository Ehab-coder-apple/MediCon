<x-superadmin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">Edit Tenant: {{ $tenant->name }}</h1>
                            <p class="mt-2 text-sm text-gray-600">Update tenant information and settings</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Details
                            </a>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Tenant Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">Internal name for the tenant</p>
                                </div>

                                <div>
                                    <label for="pharmacy_name" class="block text-sm font-medium text-gray-700">Pharmacy Name</label>
                                    <input type="text" name="pharmacy_name" id="pharmacy_name" value="{{ old('pharmacy_name', $tenant->pharmacy_name) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">Public pharmacy name</p>
                                </div>

                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="address" id="address" rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">{{ old('address', $tenant->address) }}</textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" name="city" id="city" value="{{ old('city', $tenant->city) }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    </div>

                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State/Province</label>
                                        <input type="text" name="state" id="state" value="{{ old('state', $tenant->state) }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    </div>

                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $tenant->postal_code) }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    </div>
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                    <input type="text" name="country" id="country" value="{{ old('country', $tenant->country) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Settings -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="subscription_plan" class="block text-sm font-medium text-gray-700">Subscription Plan</label>
                                    <select name="subscription_plan" id="subscription_plan" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="basic" {{ old('subscription_plan', $tenant->subscription_plan) === 'basic' ? 'selected' : '' }}>Basic</option>
                                        <option value="standard" {{ old('subscription_plan', $tenant->subscription_plan) === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="premium" {{ old('subscription_plan', $tenant->subscription_plan) === 'premium' ? 'selected' : '' }}>Premium</option>
                                        <option value="enterprise" {{ old('subscription_plan', $tenant->subscription_plan) === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="subscription_status" class="block text-sm font-medium text-gray-700">Subscription Status</label>
                                    <select name="subscription_status" id="subscription_status" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="active" {{ old('subscription_status', $tenant->subscription_status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('subscription_status', $tenant->subscription_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('subscription_status', $tenant->subscription_status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="cancelled" {{ old('subscription_status', $tenant->subscription_status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="max_users" class="block text-sm font-medium text-gray-700">Maximum Users</label>
                                    <input type="number" name="max_users" id="max_users" value="{{ old('max_users', $tenant->max_users) }}" min="1" max="1000" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="is_active" class="flex items-center">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Tenant is Active</span>
                                    </label>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="domain" class="block text-sm font-medium text-gray-700">Custom Domain (Optional)</label>
                                    <input type="text" name="domain" id="domain" value="{{ old('domain', $tenant->domain) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">e.g., pharmacy.example.com</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Update Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>
