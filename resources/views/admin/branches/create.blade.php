<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Branch') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <a href="{{ route('admin.branches.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 mb-6 font-medium transition">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to Branches
        </a>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-blue-600">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">🏪 Add New Branch</h1>
                <p class="text-gray-600">Create a new pharmacy branch with location and geofence settings</p>
            </div>

            <form method="POST" action="{{ route('admin.branches.store') }}">
                @csrf

                @include('admin.branches.form', ['branch' => null])

                <!-- Form Actions -->
                <div class="flex gap-4 mt-10 pt-8 border-t-2 border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Create Branch
                    </button>
                    <a href="{{ route('admin.branches.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 shadow-md hover:shadow-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>

