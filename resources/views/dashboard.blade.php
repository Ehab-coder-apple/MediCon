<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <img src="{{ asset('images/medicon-icon.svg') }}" alt="MediCon Icon" class="w-8 h-8 mr-3">
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                </div>
                <p class="text-gray-600">Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <div class="text-right">
                <div class="text-gray-900 font-medium">{{ now()->format('l, F j, Y') }}</div>
            </div>
        </div>
    </x-slot>

    @php
        // Redirect users to their proper role-based dashboards
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            echo '<script>window.location.href = "' . route('admin.dashboard') . '";</script>';
        } elseif ($user->hasRole('pharmacist')) {
            echo '<script>window.location.href = "' . route('pharmacist.dashboard') . '";</script>';
        } elseif ($user->hasRole('sales_staff')) {
            echo '<script>window.location.href = "' . route('sales-staff.dashboard') . '";</script>';
        }
    @endphp

    <div class="p-6">
        <!-- Redirecting Message -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Redirecting to your dashboard...</h3>
                    <p class="text-sm text-blue-700 mt-1">You will be automatically redirected to your role-specific dashboard.</p>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
