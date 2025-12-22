<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/medicon-logo.svg') }}" alt="MediCon Logo" class="h-12 w-auto">
            </div>

            <!-- Header -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Create Your Account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    @if($accessCode->access_type === 'admin_setup')
                        Set up your pharmacy administrator account for <strong>{{ $accessCode->tenant_name }}</strong>
                    @else
                        Create your user account for <strong>{{ $accessCode->tenant_name }}</strong>
                    @endif
                </p>
            </div>

            <!-- Access Code Info -->
            <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-800">Access Code Verified</p>
                        <p class="text-xs text-green-700">
                            Role: {{ ucfirst(str_replace('_', ' ', $accessCode->role_assignment)) }}
                            @if($accessCode->access_type === 'admin_setup')
                                • First-time setup
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Validation Errors -->
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('tenant.register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <x-label for="name" value="{{ __('Full Name') }}" />
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <x-label for="email" value="{{ __('Email Address') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <x-label for="phone" value="{{ __('Phone Number (Optional)') }}" />
                    <x-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                @if($accessCode->access_type === 'admin_setup')
                    <!-- Admin Setup Notice -->
                    <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <h3 class="text-sm font-medium text-purple-800 mb-2">Administrator Account</h3>
                        <p class="text-sm text-purple-700">
                            As the first administrator, you'll be able to:
                        </p>
                        <ul class="text-sm text-purple-700 mt-2 space-y-1">
                            <li>• Complete pharmacy setup and configuration</li>
                            <li>• Create additional user accounts for staff</li>
                            <li>• Manage inventory, sales, and prescriptions</li>
                            <li>• Access all administrative features</li>
                        </ul>
                    </div>
                @endif

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms" required class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 mt-1">
                        <span class="ml-2 text-sm text-gray-600">
                            I agree to the 
                            <a href="#" class="text-purple-600 hover:text-purple-500 underline">Terms of Service</a> 
                            and 
                            <a href="#" class="text-purple-600 hover:text-purple-500 underline">Privacy Policy</a>
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('access-code.form') }}">
                        {{ __('Use different code?') }}
                    </a>

                    <x-button class="ml-4">
                        @if($accessCode->access_type === 'admin_setup')
                            {{ __('Create Admin Account') }}
                        @else
                            {{ __('Create Account') }}
                        @endif
                    </x-button>
                </div>
            </form>

            <!-- Security Notice -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-800 mb-2">Security Notice</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Your password should be at least 8 characters long</li>
                    <li>• Use a combination of letters, numbers, and symbols</li>
                    <li>• Keep your login credentials secure and confidential</li>
                    @if($accessCode->access_type === 'admin_setup')
                        <li>• As an admin, you have full access to pharmacy data</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</x-guest-layout>
