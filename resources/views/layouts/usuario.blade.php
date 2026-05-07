<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Help Desk Istu - Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { "primary": "#1e3a8a", "secondary": "#84cc16" },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .dashed-button {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }

        .dashed-button:hover {
            border-color: #84cc16;
            background-color: #f7fee7;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e3a8a;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #84cc16;
        }
    </style>
</head>

<body class="bg-slate-50 font-display text-slate-900 antialiased">
    <div id="preloader"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-white transition-opacity duration-1000">
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-slate-200 border-t-secondary"></div>

            <p class="mt-4 text-sm font-black uppercase tracking-widest text-primary animate-pulse">
                Cargando sistema...
            </p>
        </div>
    </div>
    <header
        class="fixed top-0 left-0 right-0 z-[60] bg-white border-b border-slate-100 px-4 md:px-8 py-3 flex items-center justify-between shadow-sm">

        <div class="flex items-center gap-3 md:gap-4 min-w-0">
            <button id="menu-toggle" class="md:hidden p-2 rounded-lg text-primary hover:bg-slate-100 relative z-[100]">
                <span id="menu-icon" class="material-symbols-outlined">menu</span>
            </button>

            <div
                class="size-9 bg-secondary rounded-lg flex items-center justify-center text-primary shadow-sm shrink-0">
                <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
            </div>

            <div class="min-w-0">
                <h2 class="text-base md:text-xl font-bold text-primary tracking-tight truncate">
                    Help Desk Istu
                </h2>

                <span
                    class="hidden sm:inline-block text-green-900 font-bold text-[10px] md:text-xs uppercase tracking-widest px-2 py-0.5 bg-secondary/10 rounded-full truncate">
                    {{ auth()->user()->unidad->nombre_unidad ?? 'Cliente' }}
                </span>
            </div>
        </div>

        <span class="hidden md:block text-xs font-bold text-slate-400 uppercase tracking-widest max-w-[180px]">
            {{ auth()->user()->name ?? 'Cliente' }}
        </span>
    </header>

    <aside id="sidebar" class="fixed top-0 left-0 h-full w-56 xl:w-64 bg-primary border-r border-blue-800 flex flex-col pt-32 p-4
    transform -translate-x-full md:translate-x-0 transition-transform duration-300
    z-50 md:z-40">
        <nav class="space-y-1 gap-1-5 flex-1">
            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('usuario.dashboard') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.dashboard') }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('usuario.crear-ticket') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.crear-ticket') }}">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                <span class="text-sm">Crear Ticket</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('usuario.mis-tickets') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.mis-tickets') }}">
                <span class="material-symbols-outlined text-xl">history</span>
                <span class="text-sm">Mis Tickets</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('usuario.recursos') ? 'bg-primary text-secondary' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-xl font-bold transition-all"
                href="{{ route('usuario.recursos') }}">
                <span class="material-symbols-outlined text-xl">library_books</span>
                <span class="text-sm">Recursos</span>
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 text-blue-300 hover:text-blue-900 hover:bg-blue-300 rounded-xl transition-all font-bold group">
                    <span
                        class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">logout</span>
                    <span class="text-sm">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden md:hidden"></div>

    <main class="md:ml-56 xl:ml-64 pt-20 min-h-screen">
        <div class="p-4 md:p-8 max-w-[1400px] mx-auto space-y-6 md:space-y-8">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')

    <script>
        //-----------preloader 
        window.addEventListener('load', function () {
            const preloader = document.getElementById('preloader');
            preloader.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 1000);
        });
    </script>


    <script src="{{ asset('js/usuario-menu.js') }}?v={{ time() }}" defer></script>
</body>

</html>