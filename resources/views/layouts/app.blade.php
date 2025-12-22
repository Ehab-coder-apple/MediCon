<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVJkEZSMUkrQ6usKu8zIvxUsvypLcXdAawO/PzWJNJqiiicAQvAQCq6cZIEAeXByMVrQc5lrBBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <!-- Print Styles -->
        <style>
            @media print {
                /* Hide sidebar and header when printing */
                .w-64 {
                    display: none !important;
                }

                header {
                    display: none !important;
                }

                /* Adjust main content to full width */
                .min-h-screen.flex {
                    display: block !important;
                }

                .flex-1.flex.flex-col {
                    width: 100% !important;
                }

                /* Hide buttons and interactive elements */
                button, .payment-btn {
                    display: none !important;
                }

                /* Optimize for printing */
                body {
                    background: white !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                main {
                    background: white !important;
                    padding: 0 !important;
                }

                /* Remove shadows and borders for cleaner print */
                .shadow-xl, .shadow-lg, .shadow-sm {
                    box-shadow: none !important;
                }

                /* Ensure content is visible */
                .bg-gray-50, .bg-gray-100 {
                    background: white !important;
                }

                /* Optimize page margins */
                .py-12 {
                    padding-top: 0 !important;
                    padding-bottom: 0 !important;
                }

                .max-w-4xl {
                    max-width: 100% !important;
                }

                .sm\:px-6 {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .lg\:px-8 {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                /* Prevent page breaks inside elements */
                .bg-white {
                    page-break-inside: avoid !important;
                }

                /* Optimize table for printing */
                table {
                    page-break-inside: avoid !important;
                }

                tr {
                    page-break-inside: avoid !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <x-banner />

        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <div class="w-64 bg-slate-800 shadow-lg">
                <!-- Logo Section -->
                <div class="p-6 border-b border-slate-700">
                    <div class="flex items-center">
                        <img src="{{ asset('images/medicon-logo-light.svg') }}" alt="MediCon Logo" class="h-10 w-auto">
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-4 border-b border-slate-700">
                    <div class="flex items-center">
                        <div class="bg-green-500 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                            <span class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">{{ auth()->user()->name }}</p>
                            <p class="text-slate-400 text-xs">{{ ucfirst(auth()->user()->role->name ?? 'User') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-4 overflow-y-auto" style="max-height: calc(100vh - 200px);">
                    @php
                        $routePrefix = '';
                        if (auth()->user()->hasRole('admin')) {
                            $routePrefix = 'admin.';
                        } elseif (auth()->user()->hasRole('pharmacist')) {
                            $routePrefix = 'pharmacist.';
                        } elseif (auth()->user()->hasRole('sales_staff')) {
                            $routePrefix = 'sales-staff.';
                        }
                    @endphp

                    <!-- SECTION 1: Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        Dashboard
                    </a>

                    <!-- SECTION 2: Inventory -->
                    <div x-data="{ open: {{ request()->routeIs($routePrefix . 'products.*', $routePrefix . 'batches.*', 'admin.locations.*', 'admin.categories.*', 'admin.subcategories.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                <span>Inventory</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Products -->
                            <a href="{{ route($routePrefix . 'products.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'products.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Products
                            </a>
                            <!-- Batches -->
                            <a href="{{ route($routePrefix . 'batches.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'batches.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
                                </svg>
                                Batches
                            </a>
                            <!-- Locations -->
                            <a href="{{ route('admin.locations.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.locations.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Locations
                            </a>
                            <!-- Categories -->
                            <a href="{{ route('admin.categories.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM15 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/>
                                </svg>
                                Categories
                            </a>
                            <!-- Subcategories -->
                            <a href="{{ route('admin.subcategories.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.subcategories.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM15 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/>
                                </svg>
                                Subcategories
                            </a>
                        </div>
                    </div>

                    <!-- SECTION 3: Sales -->
                    <div x-data="{ open: {{ request()->routeIs($routePrefix . 'customers.*', 'invoices.*', 'admin.sales.*', $routePrefix . 'sales-returns.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                                <span>Sales</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Sales Management -->
                            <a href="{{ route('admin.sales.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.sales.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                    <path d="M16 16a2 2 0 11-4 0 2 2 0 014 0zM4 12a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Sales Management
                            </a>
                            <!-- Customers -->
                            <a href="{{ route($routePrefix . 'customers.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'customers.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                                Customers
                            </a>
                            <!-- Invoices -->
                            <a href="{{ route('invoices.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('invoices.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
                                    <path d="M6 8h8v2H6V8zM6 12h4v2H6v-2z"/>
                                </svg>
                                Invoices
                            </a>
                            <!-- Sales Returns -->
                            <a href="{{ route($routePrefix . 'sales-returns.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'sales-returns.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                    <path d="M16 16a2 2 0 11-4 0 2 2 0 014 0zM4 12a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Sales Returns
                            </a>
                        </div>
                    </div>

                    <!-- SECTION 4: Purchase -->
                    <div x-data="{ open: {{ request()->routeIs($routePrefix . 'suppliers.*', 'admin.stock-receiving.*', $routePrefix . 'purchases.*', 'admin.purchase-returns.*', 'pharmacist.purchase-returns.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                <span>Purchase</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Suppliers -->
                            <a href="{{ route($routePrefix . 'suppliers.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'suppliers.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                Suppliers
                            </a>
                            <!-- Stock Receiving -->
                            <a href="{{ route('admin.stock-receiving.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.stock-receiving.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Stock Receiving
                            </a>
                            <!-- Purchase Orders -->
                            <a href="{{ route($routePrefix . 'purchases.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'purchases.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Purchase Orders
                            </a>
                            <!-- Purchase Returns -->
                            <a href="{{ route($routePrefix . 'purchase-returns.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs($routePrefix . 'purchase-returns.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 110 14H9.828a1 1 0 110-2H11a5 5 0 100-10H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Purchase Returns
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('admin'))
                    <!-- SECTION 5: Finance -->
                    <div x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                                <span>Finance</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Reports -->
                            <a href="{{ route('admin.reports.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                                Reports
                            </a>
                        </div>
                    </div>

                    <!-- SECTION 6: Human Resources -->
                    <div x-data="{ open: {{ request()->routeIs('admin.users', 'admin.activity-logs.*', 'admin.attendance.*', 'admin.leaves.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                <span>Human Resources</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Users -->
                            <a href="{{ route('admin.users') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.users') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                Users
                            </a>
                            <!-- Attendance -->
                            <a href="{{ route('admin.attendance.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
                                </svg>
                                Attendance
                            </a>
                            <!-- Activity Logs -->
                            <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.activity-logs.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2H6a6 6 0 016 6v3.586l1.707-1.707a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L10 9.586V6a4 4 0 00-4-4H4a1 1 0 000 2h2a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V5a1 1 0 100-2h-2a1 1 0 000 2h2v11H4V5z" clip-rule="evenodd"/>
                                </svg>
                                Activity Logs
                            </a>
                            <!-- Leave Requests -->
                            <a href="{{ route('admin.leaves.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.leaves.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a1 1 0 001 1h12a1 1 0 001-1V6a2 2 0 00-2-2H4zm12 4H4v4h12V8z" clip-rule="evenodd"/>
                                </svg>
                                Leave Requests
                            </a>
                        </div>
                    </div>

                    <!-- SECTION 7: AI & Document Processing -->
                    <div x-data="{ open: {{ request()->routeIs('admin.ai.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                                <span>🤖 AI & Documents</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Dashboard -->
                            <a href="{{ route('admin.ai.dashboard') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.ai.dashboard') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Dashboard
                            </a>
                            <!-- Invoice Processing -->
                            <a href="{{ route('admin.ai.invoices.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.ai.invoices.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"/>
                                </svg>
                                Invoice Processing
                            </a>
                            <!-- Prescription Checking -->
                            <a href="{{ route('admin.ai.prescriptions.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.ai.prescriptions.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                                Prescription Checking
                            </a>
                            <!-- Product Information -->
                            <a href="{{ route('admin.ai.products.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.ai.products.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2H6a6 6 0 016 6v3.586l1.707-1.707a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L10 9.586V6a4 4 0 00-4-4H4a1 1 0 000 2h2a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V5a1 1 0 100-2h-2a1 1 0 000 2h2v11H4V5z" clip-rule="evenodd"/>
                                </svg>
                                Product Information
                            </a>
                        </div>
                    </div>

                    <!-- SECTION 7: Marketing -->
                    <div x-data="{ open: {{ request()->routeIs('whatsapp.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 4a1 1 0 011-1h6a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V4z"/>
                                    <path d="M13 2a1 1 0 00-1 1v12a1 1 0 001 1h4a1 1 0 001-1V3a1 1 0 00-1-1h-4z"/>
                                </svg>
                                <span>Marketing</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- WhatsApp Messaging -->
                            <a href="{{ route('whatsapp.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('whatsapp.*') ? 'bg-green-600 text-white border-r-2 border-green-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                WhatsApp Messaging
                            </a>
                            <!-- Offers (Placeholder for future) -->
                            <button disabled class="flex items-center px-12 py-2 text-slate-500 cursor-not-allowed text-sm opacity-50">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                                <span>Offers <span class="text-xs text-slate-600">(Coming Soon)</span></span>
                            </button>
                            <!-- Ad Creation (Placeholder for future) -->
                            <button disabled class="flex items-center px-12 py-2 text-slate-500 cursor-not-allowed text-sm opacity-50">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                                </svg>
                                <span>Ad Creation <span class="text-xs text-slate-600">(Coming Soon)</span></span>
                            </button>
                        </div>
                    </div>

                    <!-- SECTION 8: Administration -->
                    <div x-data="{ open: {{ request()->routeIs('admin.settings.*', 'admin.whatsapp.*', 'admin.branches.*', 'admin.product-display-settings.*') ? 'true' : 'false' }} }" class="mt-2">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                                <span>Administration</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" class="bg-slate-700 bg-opacity-50">
                            <!-- Pharmacy Settings -->
                            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                                Pharmacy Settings
                            </a>
                            <!-- WhatsApp Settings -->
                            <a href="{{ route('admin.whatsapp.show') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.whatsapp.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.058.3.102.605.102.924v1.902c0 .141.01.283.031.424l1.921.96a1 1 0 01.499 1.374l-.655 1.31a1 1 0 01-1.37.499l-1.921-.96A4.989 4.989 0 015 12c0-.933.127-1.836.36-2.608l-1.921-.96a1 1 0 01-.499-1.374l.655-1.31a1 1 0 011.37-.499l1.921.96c.02-.141.031-.283.031-.424V7.668c0-.319.044-.624.102-.924L3.586 5.331a1 1 0 01-.54-1.06l.74-4.435A1 1 0 015.153 3H3a1 1 0 01-1-1z"/>
                                </svg>
                                WhatsApp Settings
                            </a>
                            <!-- Branches -->
                            <a href="{{ route('admin.branches.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.branches.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Branches
                            </a>
                            <!-- Product Display Settings -->
                            <a href="{{ route('admin.product-display-settings.index') }}" class="flex items-center px-12 py-2 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors text-sm {{ request()->routeIs('admin.product-display-settings.*') ? 'bg-blue-600 text-white border-r-2 border-blue-400' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Product Display Settings
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- SECTION 9: Account -->
                    <div class="mt-4 border-t border-slate-700 pt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-6 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors text-left">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h6a1 1 0 010 2H5v10h5a1 1 0 010 2H4a1 1 0 01-1-1V4zm7.293 1.293a1 1 0 011.414 0L15 8.586l-3.293 3.293a1 1 0 01-1.414-1.414L12.586 9H9a1 1 0 110-2h3.586l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span>Log Out</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Top Header -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            @if (isset($header))
                                {{ $header }}
                            @endif
                        </div>
                        <div class="flex items-center space-x-4 px-6 py-4 bg-white">
                            <span class="text-gray-600 text-sm">{{ auth()->user()->name }}</span>
                            <div class="bg-green-500 rounded-full w-8 h-8 flex items-center justify-center">
                                <span class="text-white font-semibold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="text-gray-500 text-sm">EN</span>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 bg-gray-50 overflow-y-auto">
                    <div class="p-6">
                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </main>
            </div>
        </div>

        @stack('modals')

        @livewireScripts

        <!-- Custom Scripts -->
        @stack('scripts')
    </body>
</html>
