<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('img/admin-pro-logo.svg') }}">
    <title>Ma Boutique - Admin PRO</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        primary: {
                            50: '#f5f7ff',
                            100: '#ebf0ff',
                            200: '#d9e1ff',
                            300: '#b8c6ff',
                            400: '#8ca3ff',
                            500: '#5c7aff',
                            600: '#4759ff',
                            700: '#333bff',
                            800: '#2a31d6',
                            900: '#242aab',
                            950: '#161975',
                        },
                    },
                    borderRadius: {
                        '4xl': '2rem',
                    }
                }
            }
        }
    </script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply text-slate-900 antialiased selection:bg-primary-100 selection:text-primary-700;
            }
        }

        @layer components {
            .nav-link {
                @apply flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 font-semibold text-slate-400 hover:bg-white/5 hover:text-white;
            }

            .nav-link.active {
                @apply bg-primary-600 text-white shadow-lg shadow-primary-900/20;
            }

            .glass-card {
                @apply bg-white/80 backdrop-blur-md border border-slate-200/60 shadow-xl shadow-slate-200/40;
            }

            .btn-action {
                @apply inline-flex items-center gap-2 px-6 py-3 rounded-2xl font-bold transition-all duration-300 active:scale-95 disabled:opacity-50;
            }
        }
    </style>
</head>

<body class="h-full flex overflow-hidden bg-slate-50 relative">
    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm hidden lg:hidden transition-opacity"
        onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 w-80 bg-slate-950 flex flex-col transition-transform duration-300 transform -translate-x-full lg:relative lg:translate-x-0">
        <!-- Close button mobile -->
        <button onclick="toggleSidebar()" class="lg:hidden absolute top-6 right-6 text-slate-400 hover:text-white">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>

        <!-- Logo -->
        <div class="px-8 py-10 flex items-center gap-4">
            <div
                class="w-12 h-12 bg-primary-500 rounded-3xl flex items-center justify-center shadow-lg shadow-primary-500/20 rotate-3 transform hover:rotate-0 transition-transform duration-500">
                <i class="bi bi-shop text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight leading-none">Boutique<span
                        class="text-primary-500 italic block text-sm font-black uppercase tracking-widest mt-0.5">Admin
                        PRO</span></h1>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
            <div class="px-4 mb-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Menu Principal</div>

            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Vue d'ensemble</span>
            </a>

            <a href="{{ route('admin.boutiques.index') }}"
                class="nav-link {{ request()->routeIs('admin.boutiques.*') ? 'active' : '' }}">
                <i class="bi bi-shop-window"></i>
                <span>Réseau Boutiques</span>
            </a>

            <a href="{{ route('admin.admins.index') }}"
                class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Contrôle Accès</span>
            </a>
        </nav>

        <!-- Footer / Logout -->
        <div class="p-4 mt-auto">
            <div class="bg-white/5 rounded-3xl p-6 border border-white/5">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white leading-tight">
                            {{ auth()->user()->name ?? 'Administrateur' }}</div>
                        <div class="text-[10px] text-slate-500 font-medium truncate w-32">
                            {{ auth()->user()->email ?? 'admin@system.com' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">@csrf</form>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex items-center justify-center gap-2 w-full py-3 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-2xl transition-all duration-300 font-bold group">
                    <i class="bi bi-power text-lg group-hover:rotate-12 transition-transform"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        <!-- Top bar -->
        <header
            class="h-20 flex-shrink-0 flex items-center justify-between px-4 lg:px-10 bg-white/50 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">

            <div class="flex items-center gap-4">
                <!-- Mobile toggle button -->
                <button onclick="toggleSidebar()"
                    class="lg:hidden w-10 h-10 rounded-2xl bg-white border border-slate-200 text-slate-500 hover:text-slate-900 flex items-center justify-center shadow-sm">
                    <i class="bi bi-list text-2xl"></i>
                </button>

                <div class="hidden sm:flex items-center gap-2 text-slate-400 text-sm font-medium">
                    <i class="bi bi-house-door"></i>
                    <i class="bi bi-chevron-right text-[10px] mx-1 opacity-50"></i>
                    <span
                        class="text-slate-900 font-extrabold capitalize">{{ str_replace('.', ' / ', request()->route()->getName()) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3 lg:gap-6">
                <!-- Profile Mobile Only -->
                <div
                    class="lg:hidden w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <!-- Notifications -->
                <button
                    class="w-10 h-10 rounded-2xl bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors relative shadow-sm">
                    <i class="bi bi-bell"></i>
                    <span
                        class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 border-2 border-white rounded-full"></span>
                </button>
            </div>
        </header>

        <section class="flex-1 overflow-y-auto p-4 lg:p-10 scroll-smooth">
            <!-- Messages / Alerts -->
            @if (session('success'))
                <div
                    class="mb-8 flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-3xl animate-in fade-in slide-in-from-top-4 duration-500">
                    <div
                        class="w-10 h-10 bg-emerald-500 rounded-2xl flex items-center justify-center text-white flex-shrink-0">
                        <i class="bi bi-check2-circle text-xl"></i>
                    </div>
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="mb-8 p-6 bg-rose-50 border border-rose-100 text-rose-700 rounded-3xl animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                        <span class="font-black uppercase tracking-widest text-xs">Erreurs détectées</span>
                    </div>
                    <ul class="space-y-1 list-disc list-inside font-semibold text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>

    <script>
        // Responsive sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            sidebar.classList.toggle('-translate-x-full');
            if (sidebar.classList.contains('-translate-x-full')) {
                backdrop.classList.add('hidden');
            } else {
                backdrop.classList.remove('hidden');
            }
        }

        // Smooth reveal animations toggle
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.animate-reveal');
            elements.forEach((el, i) => {
                setTimeout(() => {
                    el.classList.add('opacity-100', 'translate-y-0');
                }, i * 100);
            });
        });
    </script>
</body>

</html>
