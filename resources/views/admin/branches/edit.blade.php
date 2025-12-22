<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Branch') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <a href="{{ route('admin.branches.show', $branch) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 mb-6 font-medium transition">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to Branch
        </a>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-yellow-600">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">✏️ Edit Branch</h1>
                <p class="text-gray-600">Update branch information: <span class="font-semibold text-gray-900">{{ $branch->name }}</span></p>
            </div>

            <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
                @csrf
                @method('PUT')

                @include('admin.branches.form', ['branch' => $branch])

                <!-- Form Actions -->
                <div class="flex gap-4 mt-10 pt-8 border-t-2 border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 shadow-md hover:shadow-lg transition transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Save Changes
                    </button>
                    <a href="{{ route('admin.branches.show', $branch) }}" class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 shadow-md hover:shadow-lg transition">
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

