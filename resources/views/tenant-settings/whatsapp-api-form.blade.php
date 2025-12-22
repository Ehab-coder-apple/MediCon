<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configure WhatsApp Business API') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">WhatsApp Business API Setup</h1>
                <p class="text-gray-600">Configure automated WhatsApp messaging with Meta's Cloud API</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">🚀 How It Works</h3>
                <p class="text-blue-800 mb-3">
                    WhatsApp Business API allows you to send automated messages through Meta's Cloud API. 
                    You'll need API credentials from Meta Business Platform.
                </p>
                <ul class="space-y-1 text-sm text-blue-800">
                    <li>✓ Full automation</li>
                    <li>✓ Bulk messaging</li>
                    <li>✓ Message templates</li>
                    <li>✓ Delivery tracking</li>
                </ul>
            </div>

            <!-- Get Credentials Link -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-yellow-900 mb-2">📋 Need API Credentials?</h3>
                <p class="text-yellow-800 mb-3">
                    This will open <strong>Meta Business Suite</strong>. In the left menu, click
                    <strong>WhatsApp Manager</strong> to create your WhatsApp Business Account,
                    connect a phone number, and get your API credentials.
                </p>
                <a
                    href="https://business.facebook.com/wa/manage/"
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                >
                    Open WhatsApp Manager in Meta Business →
                </a>
                <p class="text-yellow-800 text-sm mt-3">
                    <strong>Steps:</strong> Sign up → Create Business Account → Set up WhatsApp → Generate API Token
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form action="{{ route('admin.tenant.whatsapp.store-api') }}" method="POST">
                    @csrf

                    <!-- Business Account ID -->
                    <div class="mb-6">
                        <label for="business_account_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Business Account ID <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="business_account_id" 
                            name="business_account_id" 
                            value="{{ old('business_account_id', $credential->business_account_id ?? '') }}"
                            placeholder="Your WhatsApp Business Account ID"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('business_account_id') border-red-500 @enderror"
                            required
                        >
                        @error('business_account_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Found in Meta Business Platform → WhatsApp → Settings
                        </p>
                    </div>

                    <!-- Phone Number ID -->
                    <div class="mb-6">
                        <label for="phone_number_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Phone Number ID <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="phone_number_id" 
                            name="phone_number_id" 
                            value="{{ old('phone_number_id', $credential->phone_number_id ?? '') }}"
                            placeholder="Your WhatsApp Phone Number ID"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number_id') border-red-500 @enderror"
                            required
                        >
                        @error('phone_number_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Found in Meta Business Platform → WhatsApp → Phone Numbers
                        </p>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-6">
                        <label for="phone_number" class="block text-sm font-semibold text-gray-900 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="phone_number" 
                            name="phone_number" 
                            value="{{ old('phone_number', $credential->phone_number ?? '') }}"
                            placeholder="+20 123 456 7890"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number') border-red-500 @enderror"
                            required
                        >
                        @error('phone_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Your WhatsApp Business phone number with country code
                        </p>
                    </div>

                    <!-- Access Token -->
                    <div class="mb-6">
                        <label for="access_token" class="block text-sm font-semibold text-gray-900 mb-2">
                            Access Token <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="access_token" 
                            name="access_token" 
                            rows="4"
                            placeholder="Your WhatsApp API Access Token"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('access_token') border-red-500 @enderror"
                            required
                        >{{ old('access_token') }}</textarea>
                        @error('access_token')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Generated from Meta Business Platform → Settings → System User Tokens
                        </p>
                    </div>

                    <!-- Webhook Secret -->
                    <div class="mb-6">
                        <label for="webhook_secret" class="block text-sm font-semibold text-gray-900 mb-2">
                            Webhook Secret <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="webhook_secret" 
                            name="webhook_secret" 
                            value="{{ old('webhook_secret') }}"
                            placeholder="Your Webhook Verify Token"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('webhook_secret') border-red-500 @enderror"
                            required
                        >
                        @error('webhook_secret')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Create a secure token for webhook verification
                        </p>
                    </div>

                    <!-- Info Section -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-3">Security Notice</h3>
                        <ul class="space-y-2 text-sm text-blue-800">
                            <li>✓ Your access token will be encrypted and stored securely</li>
                            <li>✓ Never share your access token with anyone</li>
                            <li>✓ Keep your webhook secret confidential</li>
                            <li>✓ Rotate tokens regularly for security</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button 
                            type="submit" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            Save & Test API Credentials
                        </button>
                        <a
                            href="{{ route('admin.tenant.whatsapp.select-mode') }}"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-3 px-4 rounded-lg text-center transition-colors duration-200"
                        >
                            Back to Mode Selection
                        </a>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
                <div class="space-y-4 text-sm text-gray-700">
                    <div>
                        <strong>Where to find Business Account ID?</strong>
                        <p class="text-gray-600">Meta Business Platform → Settings → Business Information</p>
                    </div>
                    <div>
                        <strong>Where to find Phone Number ID?</strong>
                        <p class="text-gray-600">Meta Business Platform → WhatsApp → Phone Numbers → Select your number</p>
                    </div>
                    <div>
                        <strong>How to generate Access Token?</strong>
                        <p class="text-gray-600">Meta Business Platform → Settings → System Users → Create token with WhatsApp permissions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

