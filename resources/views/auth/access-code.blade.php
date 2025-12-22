<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please enter the access code provided by your system administrator to set up your pharmacy management system.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('access-code.verify') }}">
        @csrf

        <!-- Access Code -->
        <div>
            <x-input-label for="access_code" :value="__('Access Code')" />
            <x-text-input id="access_code" class="block mt-1 w-full text-center text-lg font-mono tracking-widest uppercase" 
                          type="text" 
                          name="access_code" 
                          :value="old('access_code')" 
                          required 
                          autofocus 
                          autocomplete="off"
                          maxlength="8"
                          placeholder="XXXXXXXX" />
            <x-input-error :messages="$errors->get('access_code')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-500">
                Enter the 8-character access code exactly as provided by your administrator.
            </p>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Verify Access Code') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Information Section -->
    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Need Help?</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>If you don't have an access code or are experiencing issues:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Contact your system administrator</li>
                        <li>Ensure you're entering the code exactly as provided</li>
                        <li>Check that the access code hasn't expired</li>
                        <li>Make sure you're using the correct code for your pharmacy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format access code input
        document.getElementById('access_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    </script>
</x-guest-layout>
