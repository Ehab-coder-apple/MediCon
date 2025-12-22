<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp Integration Setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Choose Your WhatsApp Integration Mode</h1>
                <p class="text-gray-600">Select the integration method that best fits your pharmacy's needs</p>
            </div>

            <!-- Mode Selection Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Business Free Mode Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">📱 WhatsApp Business Free</h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Status Badge -->
                        @if($currentMode === 'business_free')
                            <div class="mb-4 inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                ✓ Currently Active
                            </div>
                        @endif

                        <!-- Features List -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Features:</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span class="text-gray-700">Completely Free</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span class="text-gray-700">No API Setup Required</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span class="text-gray-700">Manual Message Sending</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span class="text-gray-700">Instant Activation</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span class="text-gray-700">Perfect for Small Pharmacies</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Limitations -->
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="font-semibold text-yellow-900 mb-2">Limitations:</h3>
                            <ul class="space-y-1 text-sm text-yellow-800">
                                <li>• No automation or bulk messaging</li>
                                <li>• Manual sending required</li>
                                <li>• No message templates</li>
                            </ul>
                        </div>

                        <!-- Best For -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-900">
                                <strong>Best for:</strong> Small pharmacies with < 100 customers who want a simple, free solution
                            </p>
                        </div>

                        <!-- Select Button -->
                        @if($currentMode !== 'business_free')
                            <form action="{{ route('admin.tenant.whatsapp.store-mode') }}" method="POST" class="mb-4">
                                @csrf
                                <input type="hidden" name="integration_type" value="business_free">
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Select Business Free Mode
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                                ✓ Currently Selected
                            </button>
                        @endif

                        <!-- Configure Button -->
                        @if($currentMode === 'business_free')
                            <a href="{{ route('admin.tenant.whatsapp.configure-business-free') }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-center transition-colors duration-200">
                                Configure Business Free
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Business API Mode Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">🚀 WhatsApp Business API</h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Status Badge -->
                        @if($currentMode === 'api')
                            <div class="mb-4 inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                ✓ Currently Active
                            </div>
                        @endif

                        <!-- Features List -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Features:</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-2">✓</span>
                                    <span class="text-gray-700">Full Automation</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-2">✓</span>
                                    <span class="text-gray-700">Bulk Messaging</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-2">✓</span>
                                    <span class="text-gray-700">Message Templates</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-2">✓</span>
                                    <span class="text-gray-700">Webhook Integration</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-2">✓</span>
                                    <span class="text-gray-700">Delivery Tracking</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Pricing -->
                        <div class="mb-6 bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h3 class="font-semibold text-purple-900 mb-2">Pricing:</h3>
                            <p class="text-sm text-purple-800">
                                $0.0079 - $0.0256 per message (varies by country)
                            </p>
                        </div>

                        <!-- Best For -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-900">
                                <strong>Best for:</strong> Growing pharmacies that need automation and scalability
                            </p>
                        </div>

                        <!-- Select Button -->
                        @if($currentMode !== 'api')
                            <form action="{{ route('admin.tenant.whatsapp.store-mode') }}" method="POST" class="mb-4">
                                @csrf
                                <input type="hidden" name="integration_type" value="api">
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Select Business API Mode
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                                ✓ Currently Selected
                            </button>
                        @endif

                        <!-- Configure Button -->
                        @if($currentMode === 'api')
                            <a href="{{ route('admin.tenant.whatsapp.configure-api') }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-center transition-colors duration-200">
                                Configure API Credentials
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gray-100 px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Feature Comparison</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Feature</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Business Free</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Business API</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Cost</td>
                                <td class="px-6 py-3 text-center text-sm text-green-600 font-semibold">Free</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-600">$0.0079-$0.0256/msg</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Setup Time</td>
                                <td class="px-6 py-3 text-center text-sm">5 minutes</td>
                                <td class="px-6 py-3 text-center text-sm">30 minutes</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Automation</td>
                                <td class="px-6 py-3 text-center text-sm">❌ Manual</td>
                                <td class="px-6 py-3 text-center text-sm">✅ Automatic</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Bulk Messaging</td>
                                <td class="px-6 py-3 text-center text-sm">❌ No</td>
                                <td class="px-6 py-3 text-center text-sm">✅ Yes</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Templates</td>
                                <td class="px-6 py-3 text-center text-sm">❌ No</td>
                                <td class="px-6 py-3 text-center text-sm">✅ Yes</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Webhooks</td>
                                <td class="px-6 py-3 text-center text-sm">❌ No</td>
                                <td class="px-6 py-3 text-center text-sm">✅ Yes</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Delivery Tracking</td>
                                <td class="px-6 py-3 text-center text-sm">❌ No</td>
                                <td class="px-6 py-3 text-center text-sm">✅ Yes</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">Best For</td>
                                <td class="px-6 py-3 text-center text-sm">Small Pharmacies</td>
                                <td class="px-6 py-3 text-center text-sm">Growing Pharmacies</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">Need Help Choosing?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-2">Choose Business Free if:</h4>
                        <ul class="space-y-1 text-sm text-blue-800">
                            <li>• You're just starting out</li>
                            <li>• You have a small customer base</li>
                            <li>• You want zero setup complexity</li>
                            <li>• You prefer manual control</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-2">Choose Business API if:</h4>
                        <ul class="space-y-1 text-sm text-blue-800">
                            <li>• You need automation</li>
                            <li>• You send bulk messages</li>
                            <li>• You want professional templates</li>
                            <li>• You need delivery tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

