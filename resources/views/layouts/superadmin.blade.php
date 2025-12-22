<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Super Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVJkEZSMUkrQ6usKu8zIvxUsvypLcXdAawO/PzWJNJqiiicAQvAQCq6cZIEAeXByMVrQc5lrBBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <x-banner />

        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <div class="w-64 bg-gray-900 shadow-lg">
                <!-- Logo Section -->
                <div class="p-6 border-b border-gray-700">
                    <div class="flex items-center">
                        <img src="{{ asset('images/medicon-logo-light.svg') }}" alt="MediCon Logo" class="h-10 w-auto">
                        <span class="ml-2 text-white text-sm font-bold">Super Admin</span>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex items-center">
                        <div class="bg-purple-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                            <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
                            <p class="text-gray-300 text-xs">Super Administrator</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-4">
                    <!-- Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center px-6 py-3 text-purple-300 hover:bg-purple-700 hover:text-white transition-colors {{ request()->routeIs('super-admin.dashboard') ? 'bg-purple-600 text-white border-r-2 border-purple-400' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Tenant Management -->
                    <a href="{{ route('super-admin.tenants.index') }}" class="flex items-center px-6 py-3 text-purple-300 hover:bg-purple-700 hover:text-white transition-colors {{ request()->routeIs('super-admin.tenants.*') ? 'bg-purple-600 text-white border-r-2 border-purple-400' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tenant Management
                    </a>

                    <!-- Access Codes -->
                    <a href="{{ route('super-admin.access-codes.index') }}" class="flex items-center px-6 py-3 text-purple-300 hover:bg-purple-700 hover:text-white transition-colors {{ request()->routeIs('super-admin.access-codes.*') ? 'bg-purple-600 text-white border-r-2 border-purple-400' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2 2 2 0 01-2 2m-2-4a2 2 0 00-2 2 2 2 0 002 2m0 0V9a2 2 0 012-2M9 7a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h4z"/>
                        </svg>
                        Access Codes
                    </a>

                    <!-- System Settings -->
                    <a href="{{ route('super-admin.settings') }}" class="flex items-center px-6 py-3 text-purple-300 hover:bg-purple-700 hover:text-white transition-colors {{ request()->routeIs('super-admin.settings') ? 'bg-purple-600 text-white border-r-2 border-purple-400' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        System Settings
                    </a>

                    <!-- Divider -->
                    <div class="border-t border-purple-700 my-4"></div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="px-6">
                        @csrf
                        <button type="submit" class="flex items-center w-full py-3 text-purple-300 hover:text-white transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Top Navigation -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                {{ $header ?? '' }}
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">{{ now()->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    <div class="p-6">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>
