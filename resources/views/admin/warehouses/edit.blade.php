<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Warehouse') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Warehouse: {{ $warehouse->name }}</h1>

                <form method="POST" action="{{ route('admin.warehouses.update', $warehouse) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('admin.warehouses.form')

                    <div class="flex justify-between items-center mt-6">
                        <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                            Back to List
                        </a>

                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                                Update Warehouse
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

