<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configure WhatsApp Business Free') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">WhatsApp Business Free Setup</h1>
                <p class="text-gray-600">Simple setup for manual WhatsApp messaging</p>
            </div>

            <!-- Info Box -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-green-900 mb-2">📱 How It Works</h3>
                <p class="text-green-800 mb-3">
                    WhatsApp Business Free mode allows you to send messages manually through WhatsApp. 
                    Simply enter your business phone number and name, and you'll get WhatsApp links to send messages.
                </p>
                <ul class="space-y-1 text-sm text-green-800">
                    <li>✓ No API setup required</li>
                    <li>✓ Completely free</li>
                    <li>✓ Instant activation</li>
                    <li>✓ Manual message sending</li>
                </ul>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form action="{{ route('admin.tenant.whatsapp.store-business-free') }}" method="POST">
                    @csrf

                    <!-- Business Phone Number -->
                    <div class="mb-6">
                        <label for="business_phone_number" class="block text-sm font-semibold text-gray-900 mb-2">
                            Business Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            id="business_phone_number"
                            name="business_phone_number"
                            value="{{ old('business_phone_number', $credential->business_phone_number ?? '') }}"
                            placeholder="+20 123 456 7890"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('business_phone_number') border-red-500 @enderror"
                            required
                        >
                        @error('business_phone_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            Enter your business phone number with country code (e.g., +20 for Egypt)
                        </p>
                    </div>

                    <!-- Business Account Name -->
                    <div class="mb-6">
                        <label for="business_account_name" class="block text-sm font-semibold text-gray-900 mb-2">
                            Business Account Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="business_account_name"
                            name="business_account_name"
                            value="{{ old('business_account_name', $credential->business_account_name ?? '') }}"
                            placeholder="Your Pharmacy Name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('business_account_name') border-red-500 @enderror"
                            required
                        >
                        @error('business_account_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-sm mt-2">
                            This name will appear in WhatsApp messages
                        </p>
                    </div>

                    <!-- Info Section -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-3">What Happens Next?</h3>
                        <ol class="space-y-2 text-sm text-blue-800">
                            <li><strong>1.</strong> You'll receive WhatsApp links for each customer</li>
                            <li><strong>2.</strong> Click the link to open WhatsApp</li>
                            <li><strong>3.</strong> Send the message manually</li>
                            <li><strong>4.</strong> Messages are logged in your system</li>
                        </ol>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            Save & Activate Business Free
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

            <!-- Example Section -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Example WhatsApp Link</h3>
                <p class="text-gray-600 mb-3">
                    After setup, you'll get links like this to send messages:
                </p>
                <div class="bg-white border border-gray-300 rounded p-4 font-mono text-sm text-gray-700 overflow-x-auto">
                    https://wa.me/201234567890?text=Hello%20from%20My%20Pharmacy
                </div>
                <p class="text-gray-600 text-sm mt-3">
                    Simply click the link and WhatsApp will open with the message ready to send!
                </p>
            </div>
        </div>
    </div>
</x-app-layout>

