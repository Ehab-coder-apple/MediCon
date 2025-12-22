<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Pharmacy Setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <!-- Welcome Message -->
                    <div class="text-center mb-8">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $user->name }}!</h1>
                        <p class="mt-2 text-gray-600">
                            Let's complete the setup of your pharmacy management system.
                        </p>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="mb-8">
                        <div class="flex items-center">
                            <div class="flex items-center text-purple-600 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 py-3 border-2 border-purple-600 bg-purple-600 text-white text-center">
                                    <span class="text-sm font-bold">1</span>
                                </div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-purple-600">Account Created</div>
                            </div>
                            <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-purple-600"></div>
                            <div class="flex items-center text-purple-600 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 py-3 border-2 border-purple-600 bg-purple-600 text-white text-center">
                                    <span class="text-sm font-bold">2</span>
                                </div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-purple-600">Pharmacy Setup</div>
                            </div>
                            <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300"></div>
                            <div class="flex items-center text-gray-500 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 py-3 border-2 border-gray-300 text-center">
                                    <span class="text-sm font-bold">3</span>
                                </div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-gray-500">Start Managing</div>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.setup.complete') }}" class="space-y-6">
                        @csrf

                        <!-- Pharmacy Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pharmacy Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="pharmacy_name" class="block text-sm font-medium text-gray-700">
                                        Pharmacy Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="pharmacy_name" id="pharmacy_name" required
                                           value="{{ old('pharmacy_name', $user->tenant->pharmacy_name) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">The official name of your pharmacy</p>
                                </div>

                                <div>
                                    <label for="pharmacy_address" class="block text-sm font-medium text-gray-700">
                                        Pharmacy Address
                                    </label>
                                    <textarea name="pharmacy_address" id="pharmacy_address" rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">{{ old('pharmacy_address', $user->tenant->address) }}</textarea>
                                    <p class="mt-1 text-sm text-gray-500">Complete address including street, city, state, and postal code</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="pharmacy_phone" class="block text-sm font-medium text-gray-700">
                                            Pharmacy Phone
                                        </label>
                                        <input type="tel" name="pharmacy_phone" id="pharmacy_phone"
                                               value="{{ old('pharmacy_phone', $user->tenant->contact_phone) }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    </div>

                                    <div>
                                        <label for="pharmacy_license" class="block text-sm font-medium text-gray-700">
                                            License Number
                                        </label>
                                        <input type="text" name="pharmacy_license" id="pharmacy_license"
                                               value="{{ old('pharmacy_license') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <p class="mt-1 text-sm text-gray-500">Your pharmacy license number</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Next Steps Information -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-blue-900 mb-4">What's Next?</h3>
                            <p class="text-sm text-blue-800 mb-3">After completing this setup, you'll be able to:</p>
                            <ul class="text-sm text-blue-700 space-y-2">
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Create user accounts for pharmacists and sales staff</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Set up your product inventory and suppliers</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Configure system settings and preferences</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Start processing sales and managing prescriptions</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                Complete Setup & Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
