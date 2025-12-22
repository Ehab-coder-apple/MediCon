@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">🤖 AI & Document Processing</h1>
        <p class="text-gray-600">Intelligent document processing and pharmacy inventory management</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total Documents -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">📄 Total Documents</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $total_documents }}</p>
                </div>
                <div class="text-blue-600 text-4xl">📊</div>
            </div>
        </div>

        <!-- Pending Documents -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-yellow-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">⏳ Pending</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $pending_documents }}</p>
                </div>
                <div class="text-yellow-600 text-4xl">⏱️</div>
            </div>
        </div>

        <!-- Processed Invoices -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">✓ Invoices</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $processed_invoices }}</p>
                </div>
                <div class="text-green-600 text-4xl">📋</div>
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-orange-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">⚠️ Pending Review</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $pending_invoices }}</p>
                </div>
                <div class="text-orange-600 text-4xl">🔍</div>
            </div>
        </div>

        <!-- Prescription Checks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">💊 Prescriptions</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $prescription_checks }}</p>
                </div>
                <div class="text-purple-600 text-4xl">📝</div>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Invoice Processing -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-600 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-900 mb-2">📋 Invoice Processing</h3>
            <p class="text-gray-600 mb-4">Upload and process purchase order invoices with AI-powered OCR</p>
            <a href="{{ route('admin.ai.invoices.index') }}" class="inline-block px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                View Invoices
            </a>
        </div>

        <!-- Prescription Checking -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-600 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-900 mb-2">💊 Prescription Checking</h3>
            <p class="text-gray-600 mb-4">Scan prescriptions and check medication availability</p>
            <a href="{{ route('admin.ai.prescriptions.index') }}" class="inline-block px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                View Prescriptions
            </a>
        </div>

        <!-- Product Information -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-600 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-900 mb-2">🔍 Product Information</h3>
            <p class="text-gray-600 mb-4">Manage pharmaceutical product data and information</p>
            <a href="{{ route('admin.ai.products.index') }}" class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                View Products
            </a>
        </div>

        <!-- Alternative Finder -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-600 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-900 mb-2">🔄 Alternative Finder</h3>
            <p class="text-gray-600 mb-4">Find alternative products with location information</p>
            <a href="{{ route('admin.ai.products.index') }}" class="inline-block px-6 py-2 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition">
                Find Alternatives
            </a>
        </div>
    </div>
</div>
@endsection

