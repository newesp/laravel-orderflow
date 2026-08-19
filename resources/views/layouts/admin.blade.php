<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | OrderFlow Lite Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col">

    <!-- Top Navigation Bar -->
    <header x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left: Logo & Nav items -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            OF
                        </div>
                        <div>
                            <span class="font-bold text-lg text-slate-900 tracking-tight">OrderFlow</span>
                            <span class="text-xs font-semibold uppercase px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 ml-1">Lite</span>
                        </div>
                    </a>

                    <nav class="hidden md:flex space-x-1">
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.products.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Products
                        </a>
                        <a href="{{ route('admin.customers.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Customers
                        </a>
                        <a href="{{ route('admin.orders.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Orders
                        </a>
                        <a href="{{ route('admin.integration_logs.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.integration_logs.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Integration Logs
                        </a>
                    </nav>
                </div>

                <!-- Right: Demo Badge, User Profile & Mobile Toggle -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span>
                        <span class="hidden sm:inline">Supabase DB Connected</span>
                        <span class="sm:hidden">DB Connected</span>
                    </span>

                    <div class="flex items-center space-x-2 sm:space-x-3 pl-2 sm:pl-3 border-l border-slate-200">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold text-slate-800">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</div>
                            <div class="text-xs text-slate-500">{{ Auth::guard('admin')->user()->email ?? '' }}</div>
                        </div>

                        <form method="POST" action="{{ route('admin.logout') }}" class="flex">
                            @csrf
                            <button type="submit"
                                    class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                    title="Log Out">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>

                        <!-- Mobile menu button -->
                        <div class="flex md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition focus:outline-none" aria-controls="mobile-menu" :aria-expanded="mobileMenuOpen">
                                <span class="sr-only">Open main menu</span>
                                <svg class="h-6 w-6" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg class="h-6 w-6" x-show="mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div class="md:hidden" id="mobile-menu" x-show="mobileMenuOpen" x-collapse style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-slate-200 bg-white">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.products.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.products.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    Products
                </a>
                <a href="{{ route('admin.customers.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    Customers
                </a>
                <a href="{{ route('admin.orders.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    Orders
                </a>
                <a href="{{ route('admin.integration_logs.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.integration_logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    Integration Logs
                </a>
            </div>
            <!-- Mobile User Profile Info -->
            <div class="pt-4 pb-3 border-t border-slate-200 sm:hidden">
                <div class="flex items-center px-5">
                    <div class="ml-3">
                        <div class="text-base font-medium text-slate-800">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</div>
                        <div class="text-sm font-medium text-slate-500">{{ Auth::guard('admin')->user()->email ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Notifications / Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 p-4 mb-4 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 p-4 mb-4 border border-red-200 text-red-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="rounded-lg bg-indigo-50 p-4 mb-4 border border-indigo-200 text-indigo-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('info') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center text-xs text-slate-500">
            <div>OrderFlow Lite &times; Modern Storefront &copy; {{ date('Y') }}</div>
            <div class="flex space-x-4">
                <span>Shared Database: PostgreSQL</span>
                <span>Role: Administrator</span>
            </div>
        </div>
    </footer>

</body>
</html>
