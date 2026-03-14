<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Toko Roni POS System - Aplikasi Point of Sale untuk Toko Roni Juntinyuat">

    <title>@yield('title', config('app.name', 'POS System'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Preconnect untuk performa -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    transitionProperty: {
                        'width': 'width',
                        'margin': 'margin',
                        'spacing': 'margin, padding',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(10px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                        slideIn: {
                            '0%': {
                                transform: 'translateX(-20px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateX(0)',
                                opacity: '1'
                            },
                        },
                    },
                }
            }
        }
    </script>
    <style>
        /* ===== SIDEBAR STYLES - OPTIMIZED ===== */
        #sidebar {
            width: 280px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 40;
            overflow-y: auto;
            overflow-x: hidden;
            will-change: width;
        }

        #sidebar.sidebar-minimized {
            width: 80px;
        }

        /* Main content adjustment */
        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 280px);
        }

        .sidebar-minimized~.main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* Mobile responsive */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                width: 280px !important;
            }

            #sidebar.sidebar-open {
                transform: translateX(0);
            }

            .main-content,
            .sidebar-minimized~.main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        /* Sidebar minimized styles */
        .sidebar-minimized .sidebar-hide {
            display: none !important;
        }

        .sidebar-minimized .sidebar-menu-item {
            justify-content: center !important;
            padding: 0.75rem !important;
            margin: 0.25rem 0.5rem !important;
        }

        .sidebar-minimized .sidebar-menu-icon {
            margin-right: 0 !important;
            width: 24px !important;
            height: 24px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-minimized .sidebar-logo {
            justify-content: center !important;
            padding: 0 !important;
        }

        .sidebar-minimized .sidebar-logo>div:first-child {
            margin-right: 0 !important;
        }

        /* Tooltip for minimized sidebar */
        .sidebar-tooltip {
            position: relative;
        }

        .sidebar-minimized .sidebar-tooltip:hover::before {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            margin-left: 0.75rem;
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            pointer-events: none;
            animation: fadeIn 0.2s ease-out;
        }

        .dark .sidebar-minimized .sidebar-tooltip:hover::before {
            background: #0f172a;
        }

        /* Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading spinner */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Toast notification */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
        }

        .toast {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease-out;
            border-left-width: 4px;
            border-left-style: solid;
        }

        .toast-success {
            border-left-color: #10b981;
            background: #f0fdf9;
        }

        .toast-error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        .toast-warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .toast-info {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Card hover effect */
        .hover-card {
            transition: all 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Active menu indicator */
        .menu-active {
            position: relative;
        }

        .menu-active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background: #3b82f6;
            border-radius: 0 4px 4px 0;
        }

        /* Fix for sidebar content when minimized */
        .sidebar-minimized nav {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .sidebar-minimized .sidebar-menu-item span:not(.sidebar-hide) {
            display: none;
        }

        /* User profile in minimized mode */
        .sidebar-minimized .user-profile-details {
            display: none;
        }

        .sidebar-minimized .user-avatar {
            margin-right: 0 !important;
        }

        /* Ensure icons are centered */
        .sidebar-menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
    </style>
    @stack('styles')
    <style> [x-cloak] { display: none !important; } </style>
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 antialiased">
    <div class="relative min-h-screen">
        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay"
            class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden hidden transition-opacity duration-300">
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col bg-white dark:bg-gray-800 shadow-xl">
            <!-- Logo -->
            <div
                class="h-16 px-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-3 sidebar-logo">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-store text-sm text-white"></i>
                    </div>
                    <div class="sidebar-hide">
                        <div class="font-bold text-gray-800 dark:text-white text-sm">TOKO RONI</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Toko Roni Juntinyuat</div>
                    </div>
                </div>
                <button id="sidebar-close"
                    class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Profile -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="relative flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-base shadow-md">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div
                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full">
                        </div>
                    </div>
                    <div class="sidebar-hide overflow-hidden">
                        <div class="font-medium text-gray-800 dark:text-white text-sm truncate">{{ Auth::user()->name }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400" id="greeting-text">Good Night!</div>
                        <div class="mt-1">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <i class="fas fa-shield-alt mr-1 text-[8px]"></i>
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto scrollbar-thin py-4 px-3">
                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                        data-tooltip="Dashboard">
                        <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                            <i class="fas fa-home text-sm"></i>
                        </div>
                        <span class="sidebar-hide text-sm font-medium">Dashboard</span>
                    </a>

                    @if (Auth::user()->role === 'owner')
                        <!-- Management Section -->
                        <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="px-3 mb-1">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Management
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('users.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Users">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-users text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Users</span>
                        </a>

                        <a href="{{ route('members.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('members.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Member">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Member</span>
                        </a>

                        <a href="{{ route('reports.sales') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Reports">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-chart-line text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Reports</span>
                        </a>

                        <a href="{{ route('transactions.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('transactions.index') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Transaction History">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-receipt text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Transaction History</span>
                        </a>
                    @endif

                    @if (in_array(Auth::user()->role, ['kasir', 'owner']))
                        <!-- Transaction Section -->
                        <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="px-3 mb-1">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Transaction</div>
                            </div>
                        </div>

                        <a href="{{ route('transactions.create') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('transactions.create') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Cashier">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-cash-register text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Cashier</span>
                        </a>
                    @endif

                    @if (in_array(Auth::user()->role, ['owner']))
                        <!-- Inventory Section -->
                        <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="px-3 mb-1">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Inventory
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('products.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Products">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-box text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Products</span>
                        </a>

                        <a href="{{ route('categories.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Categories">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-tags text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Categories</span>
                        </a>
                    @endif

                    @if (in_array(Auth::user()->role, ['owner', 'manager', 'kepala_gudang', 'checker_barang']))
                        <!-- Inventory Section -->
                        <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="px-3 mb-1">
                                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Inventory
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('products.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Products">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-box text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Products</span>
                        </a>

                        @if (in_array(Auth::user()->role, ['owner', 'manager', 'kepala_gudang']))
                        <a href="{{ route('categories.index') }}"
                            class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                            data-tooltip="Categories">
                            <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                                <i class="fas fa-tags text-sm"></i>
                            </div>
                            <span class="sidebar-hide text-sm font-medium">Categories</span>
                        </a>
                        @endif
        @endif

                    <!-- Delivery Section -->
                    @if (in_array(Auth::user()->role, ['owner', 'logistik','admin']))
                    <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                        <div class="px-3 mb-1">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Delivery
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('delivery.index') }}"
                        class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('delivery.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                        data-tooltip="Delivery">
                        <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                            <i class="fas fa-truck text-sm"></i>
                        </div>
                        <span class="sidebar-hide text-sm font-medium">Delivery</span>
                    </a>
                    @endif

                    <!-- Vehicle Section -->
                    @if (in_array(Auth::user()->role, ['owner', 'logistik','admin']))
                    <a href="{{ route('vehicles.index') }}"
                        class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('vehicles.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                        data-tooltip="Vehicle">
                        <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                            <i class="fas fa-car text-sm"></i>
                        </div>
                        <span class="sidebar-hide text-sm font-medium">Vehicle</span>
                    </a>
                    @endif

                    <!-- Account Section -->
                    <div class="sidebar-hide pt-3 mt-2 border-t border-gray-100 dark:border-gray-700">
                        <div class="px-3 mb-1">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Account</div>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                        class="sidebar-tooltip sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                        data-tooltip="Profile">
                        <div class="sidebar-menu-icon w-5 h-5 flex items-center justify-center mr-3">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span class="sidebar-hide text-sm font-medium">Profile</span>
                    </a>
                </nav>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 space-y-3 flex-shrink-0">
                <!-- Theme Toggle -->
                <div class="flex items-center justify-between sidebar-hide">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Theme</span>
                    <div class="flex items-center space-x-1">
                        <button id="theme-light"
                            class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            title="Light Mode">
                            <i class="fas fa-sun text-yellow-500 text-sm"></i>
                        </button>
                        <button id="theme-dark"
                            class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            title="Dark Mode">
                            <i class="fas fa-moon text-gray-600 dark:text-gray-400 text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Minimize Toggle -->
                <button id="sidebar-minimize"
                    class="sidebar-tooltip w-full flex items-center justify-center px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200"
                    data-tooltip="Minimize Sidebar">
                    <i class="fas fa-chevron-left text-gray-500 text-sm"></i>
                    <span
                        class="sidebar-hide ml-2 text-xs font-medium text-gray-600 dark:text-gray-400">Minimize</span>
                </button>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="button" onclick="confirmLogout()"
                        class="sidebar-tooltip w-full flex items-center justify-center px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-all duration-200"
                        data-tooltip="Logout">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                        <span class="sidebar-hide ml-2 text-xs font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header
                class="bg-white dark:bg-gray-800 shadow-sm h-14 px-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center space-x-3">
                    <button id="sidebar-toggle"
                        class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    <!-- Expand button (shown when sidebar minimized) -->
                    <button id="sidebar-expand"
                        class="hidden items-center px-2 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                        <i class="fas fa-chevron-right text-gray-600 dark:text-gray-300 text-sm"></i>
                    </button>

                    <div>
                        <h1 class="text-base font-bold text-gray-800 dark:text-white">
                            @hasSection('title')
                                @yield('title')
                            @else
                                Dashboard
                            @endif
                        </h1>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            <span id="greeting">{{ $greeting ?? 'Good Night' }}</span>, {{ Auth::user()->name }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Notifications Dropdown -->
                    <x-notification-dropdown />
                    
                    <!-- User Profile -->
                    <div class="flex items-center space-x-2 cursor-pointer hover:opacity-80 transition-opacity">
                        <div class="relative">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div
                                class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full">
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="font-medium text-gray-800 dark:text-white text-sm">{{ Auth::user()->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst(Auth::user()->role) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 md:p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/30 dark:border-green-800 animate-fade-in"
                        role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-sm"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-green-800 dark:text-green-300 text-sm font-medium">
                                    {{ session('success') }}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()"
                                class="ml-auto text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/30 dark:border-red-800 animate-fade-in"
                        role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-sm"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-red-800 dark:text-red-300 text-sm font-medium">{{ session('error') }}
                                </p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()"
                                class="ml-auto text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-yellow-900/30 dark:border-yellow-800 animate-fade-in"
                        role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i
                                    class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-sm"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-yellow-800 dark:text-yellow-300 text-sm font-medium">
                                    {{ session('warning') }}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()"
                                class="ml-auto text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/30 dark:border-blue-800 animate-fade-in"
                        role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-sm"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-blue-800 dark:text-blue-300 text-sm font-medium">{{ session('info') }}
                                </p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()"
                                class="ml-auto text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-5 h-5 rounded bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center">
                                <i class="fas fa-store text-[8px] text-white"></i>
                            </div>
                            <div>
                                <p class="font-medium">© {{ date('Y') }} TOKO RONI</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-500">Toko Roni Juntinyuat • Since
                                    2023</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 md:mt-0 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt mr-1 text-blue-500 text-[10px]"></i>
                                <span class="font-medium">{{ ucfirst(Auth::user()->role) }} Access</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt mr-1 text-blue-500 text-[10px]"></i>
                                <span id="current-date" class="font-medium">{{ now()->format('l, d F Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Toast Container (for dynamic notifications) -->
    <div id="toast-container" class="toast-container"></div>
        <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        // ================= SIDEBAR TOGGLE =================
        (function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarClose = document.getElementById('sidebar-close');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const sidebarMinimize = document.getElementById('sidebar-minimize');
            const sidebarExpand = document.getElementById('sidebar-expand');

            // Mobile Toggle
            function openSidebarMobile() {
                sidebar.classList.add('sidebar-open');
                sidebarOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebarMobile() {
                sidebar.classList.remove('sidebar-open');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', openSidebarMobile);
            }

            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebarMobile);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebarMobile);
            }

            // Minimize/Expand Toggle
            function toggleSidebarMinimize() {
                sidebar.classList.toggle('sidebar-minimized');

                // Update icon and text
                const icon = sidebarMinimize.querySelector('i');
                const text = sidebarMinimize.querySelector('span:not(.sidebar-hide)');

                if (sidebar.classList.contains('sidebar-minimized')) {
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                    if (text) text.textContent = 'Expand';
                    if (sidebarExpand) sidebarExpand.classList.remove('hidden');
                    sidebarMinimize.setAttribute('data-tooltip', 'Expand Sidebar');
                } else {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                    if (text) text.textContent = 'Minimize';
                    if (sidebarExpand) sidebarExpand.classList.add('hidden');
                    sidebarMinimize.setAttribute('data-tooltip', 'Minimize Sidebar');
                }

                // Save state
                localStorage.setItem('sidebarMinimized', sidebar.classList.contains('sidebar-minimized'));

                // Trigger resize event for charts to adjust
                window.dispatchEvent(new Event('resize'));
            }

            if (sidebarMinimize) {
                sidebarMinimize.addEventListener('click', toggleSidebarMinimize);
            }

            // Expand button in header
            if (sidebarExpand) {
                sidebarExpand.addEventListener('click', () => {
                    sidebar.classList.remove('sidebar-minimized');
                    const icon = sidebarMinimize.querySelector('i');
                    const text = sidebarMinimize.querySelector('span:not(.sidebar-hide)');
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                    if (text) text.textContent = 'Minimize';
                    sidebarExpand.classList.add('hidden');
                    sidebarMinimize.setAttribute('data-tooltip', 'Minimize Sidebar');
                    localStorage.setItem('sidebarMinimized', false);
                    window.dispatchEvent(new Event('resize'));
                });
            }

            // Initialize sidebar state
            document.addEventListener('DOMContentLoaded', () => {
                const isMinimized = localStorage.getItem('sidebarMinimized') === 'true';
                if (isMinimized && sidebarMinimize) {
                    sidebar.classList.add('sidebar-minimized');
                    const icon = sidebarMinimize.querySelector('i');
                    const text = sidebarMinimize.querySelector('span:not(.sidebar-hide)');
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                    if (text) text.textContent = 'Expand';
                    if (sidebarExpand) sidebarExpand.classList.remove('hidden');
                    sidebarMinimize.setAttribute('data-tooltip', 'Expand Sidebar');
                }
            });

            // Close sidebar on window resize if screen becomes large
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('sidebar-open');
                    if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        })();

        // ================= THEME TOGGLE =================
        (function() {
            const themeLight = document.getElementById('theme-light');
            const themeDark = document.getElementById('theme-dark');

            function setTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                localStorage.setItem('theme', theme);
            }

            // Initialize theme
            document.addEventListener('DOMContentLoaded', () => {
                const savedTheme = localStorage.getItem('theme') ||
                    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                setTheme(savedTheme);

                if (themeLight) {
                    themeLight.addEventListener('click', () => setTheme('light'));
                }

                if (themeDark) {
                    themeDark.addEventListener('click', () => setTheme('dark'));
                }

                // Listen for system theme changes
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (!localStorage.getItem('theme')) {
                        setTheme(e.matches ? 'dark' : 'light');
                    }
                });
            });
        })();

        // ================= GREETING =================
        function updateGreeting() {
            const now = new Date();
            const hour = now.getHours();
            let greeting = '';

            if (hour >= 5 && hour < 12) greeting = 'Good Morning';
            else if (hour >= 12 && hour < 15) greeting = 'Good Afternoon';
            else if (hour >= 15 && hour < 18) greeting = 'Good Evening';
            else greeting = 'Good Night';

            const greetingElement = document.getElementById('greeting');
            const greetingText = document.getElementById('greeting-text');
            if (greetingElement) greetingElement.textContent = greeting;
            if (greetingText) greetingText.textContent = greeting + '!';
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateGreeting();
            setInterval(updateGreeting, 60000);
        });

        // ================= DATE UPDATE =================
        function updateDate() {
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                dateElement.textContent = now.toLocaleDateString('id-ID', options);
            }
        }

        document.addEventListener('DOMContentLoaded', updateDate);
        setInterval(updateDate, 60000);

        // ================= LOGOUT CONFIRMATION =================
        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                document.getElementById('logout-form').submit();
            }
        }

        // ================= TOAST NOTIFICATION FUNCTION =================
        function showToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `
                <i class="fas ${icons[type]} text-sm"></i>
                <span class="flex-1 text-sm">${message}</span>
                <button onclick="this.parentElement.remove()" class="hover:opacity-70">
                    <i class="fas fa-times text-xs"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        window.showToast = showToast;
    </script>

    @stack('scripts')
</body>

</html>
