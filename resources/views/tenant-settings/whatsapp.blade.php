<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('WhatsApp Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Dual Mode Setup Button -->
            <div class="mb-6">
                <a href="{{ route('admin.tenant.whatsapp.select-mode') }}" class="inline-block bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                    🔄 Switch to Dual Mode Setup
                </a>
                <p class="text-sm text-gray-600 mt-2">
                    Use our new dual-mode setup to choose between WhatsApp Business Free (manual) or Business API (automated)
                </p>
            </div>

            <!-- Current Mode Card -->
            @if($credential && $credential->integration_type)
                <div class="overflow-hidden shadow-2xl sm:rounded-lg mb-6 {{ $credential->integration_type === 'business_free' ? 'bg-gradient-to-br from-green-400 via-green-300 to-green-200 border-l-8 border-green-700' : 'bg-gradient-to-br from-blue-400 via-blue-300 to-blue-200 border-l-8 border-blue-700' }}">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-8">📡 Current Integration Mode</h3>

                        <div class="flex items-start justify-between gap-8">
                            <div class="flex-1">
                                @if($credential->integration_type === 'business_free')
                                    <div class="flex items-start gap-6">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center h-20 w-20 rounded-xl bg-green-700 text-white text-4xl shadow-2xl">
                                                📱
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-green-900 mb-2">
                                                WhatsApp Business Free
                                            </p>
                                            <p class="text-base text-green-800 font-bold mb-4 bg-white bg-opacity-60 px-3 py-1 rounded-lg inline-block">
                                                ✋ Manual messaging mode
                                            </p>
                                            @if($credential->business_phone_number)
                                                <p class="text-base text-green-900 mb-2 font-semibold">
                                                    <strong>📞 Phone:</strong> {{ $credential->business_phone_number }}
                                                </p>
                                            @endif
                                            @if($credential->business_account_name)
                                                <p class="text-base text-green-900 font-semibold">
                                                    <strong>🏢 Account:</strong> {{ $credential->business_account_name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($credential->integration_type === 'api')
                                    <div class="flex items-start gap-6">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center h-20 w-20 rounded-xl bg-blue-700 text-white text-4xl shadow-2xl">
                                                🚀
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-blue-900 mb-2">
                                                WhatsApp Business API
                                            </p>
                                            <p class="text-base text-blue-800 font-bold mb-4 bg-white bg-opacity-60 px-3 py-1 rounded-lg inline-block">
                                                ⚡ Automated messaging mode
                                            </p>
                                            @if($credential->phone_number)
                                                <p class="text-base text-blue-900 font-semibold">
                                                    <strong>📞 Phone:</strong> {{ $credential->phone_number }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-4 flex-shrink-0">
                                <a href="{{ route('admin.tenant.whatsapp.select-mode') }}" class="block w-full bg-gradient-to-r from-indigo-500 to-indigo-700 hover:from-indigo-600 hover:to-indigo-800 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-200 text-center shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 hover:scale-105">
                                    🔄 Change Mode
                                </a>
                                @if($credential->integration_type === 'business_free')
                                    <a href="{{ route('admin.tenant.whatsapp.configure-business-free') }}" class="block w-full bg-gradient-to-r from-green-500 to-green-700 hover:from-green-600 hover:to-green-800 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-200 text-center shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 hover:scale-105">
                                        ⚙️ Configure
                                    </a>
                                @elseif($credential->integration_type === 'api')
                                    <a href="{{ route('admin.tenant.whatsapp.configure-api') }}" class="block w-full bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-200 text-center shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 hover:scale-105">
                                        ⚙️ Configure
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Enabled Status -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($stats['is_enabled'])
                                    <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Status: <span class="{{ $stats['is_enabled'] ? 'text-green-600' : 'text-red-600' }} font-bold">
                                        {{ $stats['is_enabled'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Verified Status -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($stats['is_verified'])
                                    <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Verified: <span class="{{ $stats['is_verified'] ? 'text-green-600' : 'text-yellow-600' }} font-bold">
                                        {{ $stats['is_verified'] ? 'Yes' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.058.3.102.605.102.924v1.902c0 .141.01.283.031.424l1.921.96a1 1 0 01.499 1.374l-.655 1.31a1 1 0 01-1.37.499l-1.921-.96A4.989 4.989 0 015 12c0-.933.127-1.836.36-2.608l-1.921-.96a1 1 0 01-.499-1.374l.655-1.31a1 1 0 011.37-.499l1.921.96c.02-.141.031-.283.031-.424V7.668c0-.319.044-.624.102-.924L3.586 5.331a1 1 0 01-.54-1.06l.74-4.435A1 1 0 015.153 3H3a1 1 0 01-1-1z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Phone: <span class="text-gray-600">{{ $stats['phone_number'] }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Last Tested -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Last Tested: <span class="text-gray-600">
                                        {{ $stats['last_tested_at'] ? $stats['last_tested_at']->diffForHumans() : 'Never' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100-2 1 1 0 000 2zm5-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Need WhatsApp credentials?</strong> Visit the
                            <a href="https://business.facebook.com" target="_blank" rel="noopener noreferrer" class="font-semibold underline hover:text-blue-900">
                                Meta Business Platform
                            </a>
                            to create your WhatsApp Business Account and get your API credentials.
                        </p>
                        <p class="text-sm text-blue-700 mt-2">
                            <strong>Steps:</strong> Sign up → Create Business Account → Set up WhatsApp → Generate API Token
                        </p>
                    </div>
                </div>
            </div>

            <!-- Credentials Form -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">WhatsApp Credentials</h3>
                    
                    <form action="{{ route('admin.whatsapp.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Business Account ID -->
                        <div>
                            <label for="business_account_id" class="block text-sm font-medium text-gray-700">
                                Business Account ID
                            </label>
                            <input type="text" name="business_account_id" id="business_account_id"
                                value="{{ old('business_account_id', $credential?->business_account_id) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('business_account_id') border-red-500 @enderror"
                                placeholder="Your WhatsApp Business Account ID">
                            @error('business_account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number ID -->
                        <div>
                            <label for="phone_number_id" class="block text-sm font-medium text-gray-700">
                                Phone Number ID
                            </label>
                            <input type="text" name="phone_number_id" id="phone_number_id"
                                value="{{ old('phone_number_id', $credential?->phone_number_id) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('phone_number_id') border-red-500 @enderror"
                                placeholder="Your WhatsApp Phone Number ID">
                            @error('phone_number_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">
                                Phone Number
                            </label>
                            <input type="text" name="phone_number" id="phone_number"
                                value="{{ old('phone_number', $credential?->phone_number) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('phone_number') border-red-500 @enderror"
                                placeholder="+20 1234567890">
                            @error('phone_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Access Token -->
                        <div>
                            <label for="access_token" class="block text-sm font-medium text-gray-700">
                                Access Token
                            </label>
                            <textarea name="access_token" id="access_token"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('access_token') border-red-500 @enderror"
                                rows="3"
                                placeholder="Your WhatsApp API Access Token">{{ old('access_token') }}</textarea>
                            @error('access_token')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Webhook Secret -->
                        <div>
                            <label for="webhook_secret" class="block text-sm font-medium text-gray-700">
                                Webhook Secret
                            </label>
                            <input type="text" name="webhook_secret" id="webhook_secret"
                                value="{{ old('webhook_secret') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('webhook_secret') border-red-500 @enderror"
                                placeholder="Your Webhook Verification Token">
                            @error('webhook_secret')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                💾 Save Credentials
                            </button>
                            @if($credential && $credential->isComplete())
                                <form action="{{ route('admin.whatsapp.test') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        ✓ Verify Credentials
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enable/Disable Section -->
            @if($credential && $credential->is_verified)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">WhatsApp Messaging</h3>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">
                                    {{ $stats['is_enabled'] ? 'WhatsApp messaging is currently enabled.' : 'WhatsApp messaging is currently disabled.' }}
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                @if(!$stats['is_enabled'])
                                    <form action="{{ route('admin.whatsapp.enable') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                            ✓ Enable
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.whatsapp.disable') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                            ✗ Disable
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Guide Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📖 Credential Guide</h3>

                    <div class="space-y-4">
                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-900">Business Account ID</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Found in Meta Business Platform → Settings → Business Info. This identifies your business account.
                            </p>
                        </div>

                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-900">Phone Number ID</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Found in WhatsApp → Phone Numbers. This is the unique identifier for your WhatsApp phone number.
                            </p>
                        </div>

                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-900">Phone Number</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Your WhatsApp business phone number (e.g., +20 1234567890). This is the number customers will see messages from.
                            </p>
                        </div>

                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-900">Access Token</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Generated from Meta Business Platform → Settings → System Users. This token authenticates API requests. Keep it secret!
                            </p>
                        </div>

                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-900">Webhook Secret</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Found in WhatsApp → Configuration → Webhook. This verifies that webhook messages are from Meta.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>⚠️ Security Note:</strong> Your credentials are encrypted and stored securely. Never share your Access Token or Webhook Secret with anyone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

