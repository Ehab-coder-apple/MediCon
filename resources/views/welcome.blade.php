<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MediCon - Complete Pharmacy Management SaaS</title>
        <meta name="description" content="Streamline your pharmacy operations with our comprehensive multi-tenant platform. Complete inventory management, sales tracking, and team collaboration tools.">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            }
            .program-owner-card {
                background: linear-gradient(135deg, #6b46c1 0%, #3730a3 100%) !important;
                color: white !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
                border: 2px solid #8b5cf6 !important;
            }
            .pharmacy-card {
                background: linear-gradient(135deg, #047857 0%, #0f766e 100%) !important;
                color: white !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
                border: 2px solid #10b981 !important;
            }
            .card-text {
                color: white !important;
                font-weight: 500 !important;
            }
            .card-title {
                color: white !important;
                font-weight: 700 !important;
            }
            .card-subtitle {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            .demo-box {
                background: rgba(255, 255, 255, 0.2) !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                backdrop-filter: blur(10px);
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <img src="{{ asset('images/medicon-logo.svg') }}" alt="MediCon Logo" class="h-12 w-auto">
                        </div>
                    </div>
                    
                    <!-- Navigation Links -->
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md font-medium transition-colors">
                                    Sign In
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="gradient-bg text-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <!-- Hero Logo -->
                    <div class="mb-8">
                        <img src="{{ asset('images/medicon-logo.svg') }}" alt="MediCon Logo" class="h-20 w-auto mx-auto">
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        Complete Pharmacy<br>
                        <span class="text-yellow-300">Management SaaS</span>
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto">
                        Streamline your pharmacy operations with our comprehensive multi-tenant platform. 
                        Complete inventory management, sales tracking, and team collaboration tools.
                    </p>
                    
                    <!-- Key Benefits -->
                    <div class="flex flex-wrap justify-center gap-6 mb-12">
                        <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                            <svg class="w-5 h-5 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Multi-Tenant Architecture</span>
                        </div>
                        <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                            <svg class="w-5 h-5 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Custom Role Management</span>
                        </div>
                        <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                            <svg class="w-5 h-5 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Complete Analytics</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Access Portals Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Access Level</h2>
                    <p class="text-xl text-gray-700 max-w-2xl mx-auto font-medium">
                        Access the platform based on your role and responsibilities
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Program Owner Portal -->
                    <div class="card-hover program-owner-card rounded-2xl p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-white bg-opacity-25 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl card-title">Program Owner</h3>
                                <p class="card-subtitle">Platform Management</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Manage all pharmacy tenants</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Platform-wide analytics</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Subscription management</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Support & impersonation tools</span>
                            </div>
                        </div>

                        <a href="{{ route('login') }}" class="block w-full bg-white text-purple-700 text-center py-3 px-6 rounded-lg font-bold hover:bg-gray-100 transition-colors shadow-lg">
                            Login as Program Owner
                        </a>

                        <div class="mt-4 p-3 demo-box rounded-lg">
                            <p class="text-sm font-bold mb-1 card-text">Demo Credentials:</p>
                            <p class="text-xs card-subtitle font-medium">Email: superadmin@medicon.com</p>
                            <p class="text-xs card-subtitle font-medium">Password: password</p>
                        </div>
                    </div>

                    <!-- Pharmacy Portal -->
                    <div class="card-hover pharmacy-card rounded-2xl p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-white bg-opacity-25 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl card-title">Pharmacy Access</h3>
                                <p class="card-subtitle">Tenant Management</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Manage your pharmacy operations</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Custom role management</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Team collaboration tools</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="card-text">Complete inventory & sales system</span>
                            </div>
                        </div>

                        <a href="{{ route('login') }}" class="block w-full bg-white text-green-700 text-center py-3 px-6 rounded-lg font-bold hover:bg-gray-100 transition-colors shadow-lg">
                            Login to Your Pharmacy
                        </a>

                        <div class="mt-4 p-3 demo-box rounded-lg">
                            <p class="text-sm font-bold mb-1 card-text">Demo Credentials:</p>
                            <p class="text-xs card-subtitle font-medium">Email: admin@demo-pharmacy.com</p>
                            <p class="text-xs card-subtitle font-medium">Password: password</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Comprehensive Pharmacy Management</h2>
                    <p class="text-xl text-gray-700 max-w-3xl mx-auto font-medium">
                        Everything you need to run a modern pharmacy efficiently and profitably
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Multi-Tenant Architecture -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Multi-Tenant SaaS</h3>
                        <p class="text-gray-700 font-medium">Complete tenant isolation with custom domains, role management, and subscription controls.</p>
                    </div>

                    <!-- Inventory Management -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Inventory Management</h3>
                        <p class="text-gray-700 font-medium">Track stock levels, batch numbers, expiry dates with automated alerts and supplier management.</p>
                    </div>

                    <!-- Sales & POS -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Sales & POS</h3>
                        <p class="text-gray-700 font-medium">Complete point-of-sale system with barcode scanning, invoice generation, and payment processing.</p>
                    </div>

                    <!-- Customer Management -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Customer Management</h3>
                        <p class="text-gray-700 font-medium">Maintain customer profiles, purchase history, and prescription records with privacy controls.</p>
                    </div>

                    <!-- Prescription Management -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Prescription Management</h3>
                        <p class="text-gray-700 font-medium">Secure prescription upload, pharmacist approval workflow, and compliance tracking.</p>
                    </div>

                    <!-- Analytics & Reports -->
                    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Analytics & Reports</h3>
                        <p class="text-gray-700 font-medium">Comprehensive business intelligence with sales analytics, inventory reports, and financial insights.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Trusted by Pharmacies Worldwide</h2>
                    <p class="text-xl text-gray-700 font-medium">Join thousands of pharmacies already using MediCon</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2">500+</div>
                        <div class="text-gray-700 font-medium">Active Pharmacies</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600 mb-2">1M+</div>
                        <div class="text-gray-700 font-medium">Prescriptions Processed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600 mb-2">99.9%</div>
                        <div class="text-gray-700 font-medium">Uptime Guarantee</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-yellow-600 mb-2">24/7</div>
                        <div class="text-gray-700 font-medium">Support Available</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <div class="flex items-center mb-4">
                            <img src="{{ asset('images/medicon-logo-light.svg') }}" alt="MediCon Logo" class="h-8 w-auto">
                        </div>
                        <p class="text-gray-300 mb-4 font-medium">
                            The complete multi-tenant pharmacy management solution.
                            Streamline operations, manage inventory, and grow your business.
                        </p>
                        <div class="text-sm text-gray-300">
                            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 text-white">Platform</h4>
                        <ul class="space-y-2 text-gray-300">
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Features</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Pricing</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Security</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">API</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 text-white">Support</h4>
                        <ul class="space-y-2 text-gray-300">
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Documentation</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Contact Us</a></li>
                            <li><a href="#" class="hover:text-white transition-colors font-medium">Status</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                    <p class="font-medium">&copy; {{ date('Y') }} MediCon. All rights reserved. Built with Laravel & Tailwind CSS.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
