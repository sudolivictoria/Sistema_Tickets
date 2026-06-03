<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Help Desk Istu - Usuario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    @stack('css')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            background: #04003B;
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
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-slate-200 border-t-primary"></div>

            <p class="mt-4 text-sm font-black uppercase tracking-widest text-secondary animate-pulse">
                Cargando sistema...
            </p>
        </div>
    </div>
    <header
        class="fixed top-0 left-0 right-0 z-[60] bg-white border-b border-slate-100 px-4 lg:px-8 py-3 flex items-center justify-between shadow-sm">

        <div class="flex items-center gap-3 lg:gap-4 min-w-0">
            <button id="menu-toggle"
                class="lg:hidden p-2 rounded-lg text-secondary hover:bg-slate-100 relative z-[100]">
                <span id="menu-icon" class="material-symbols-outlined">menu</span>
            </button>

            <div
                class="size-9 bg-primary rounded-lg flex items-center justify-center text-secondary shadow-sm shrink-0">
                <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
            </div>

            <div class="min-w-0">
                <h2 class="text-xl font-bold text-secondary tracking-tight">Help Desk Istu <span
                        class="text-green-900 text-xs uppercase ml-2 tracking-widest px-2 py-0.5 bg-primary/10 rounded-full">{{ auth()->user()->unidad->nombre_unidad ?? 'Usuario' }}</span>
                </h2>
            </div>
        </div>

        <div
            class="hidden md:flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
            <span class="material-symbols-outlined text-slate-400 text-[18px] animate-pulse">schedule</span>
            <span id="relojSistema" class="text-xs font-black text-slate-600 tracking-wider">00:00:00</span>
        </div>
    </header>

    <aside id="sidebar"
        class="fixed top-0 left-0 h-full w-56 xl:w-64 bg-secondary border-r border-blue-800 flex flex-col pt-32 p-4
    transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 lg:z-40">
        <nav class="space-y-3 flex-1">
            <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('usuario.dashboard') ? 'bg-primary text-secondary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.dashboard') }}">
                <span class="material-symbols-outlined text-xl">dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>
            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-1 px-4 font-black">
                Servicios</p>
            <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('usuario.crear-ticket') ? 'bg-primary text-secondary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.crear-ticket') }}">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                <span class="text-sm">Crear Ticket</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('usuario.mis-tickets') ? 'bg-primary text-secondary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('usuario.mis-tickets') }}">
                <span class="material-symbols-outlined text-xl">history</span>
                <span class="text-sm">Mis Tickets</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('usuario.recursos') ? 'bg-primary text-secondary' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-xl font-bold transition-all"
                href="{{ route('usuario.recursos') }}">
                <span class="material-symbols-outlined text-xl">library_books</span>
                <span class="text-sm">Recursos</span>
            </a>
        </nav>

        <div class="mt-auto pt-2 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 text-blue-300 hover:text-[#04003B] hover:bg-blue-300 rounded-xl transition-all font-bold group">
                    <span
                        class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">logout</span>
                    <span class="text-sm">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    <main class="lg:ml-56 xl:ml-64 pt-20 min-h-screen">
        <div class="p-4 lg:p-8 max-w-[1400px] mx-auto space-y-6 lg:space-y-8">
            @yield('content')
        </div>
    </main>

    <script>
        //-----------preloader 
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 1000);
        });

        //-----reloj 
        window.iniciarReloj = function() {
            const contenedorReloj = document.getElementById('relojSistema');
            if (!contenedorReloj) return;

            //---actualizar hora cada segundo
            const actualizarHora = () => {
                const ahora = new Date();
                contenedorReloj.innerText = ahora.toLocaleTimeString('es-SV', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            };

            //-----sin delay
            actualizarHora();

            //-----corre cada segundo
            setInterval(actualizarHora, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            iniciarReloj();
        });
    </script>

    @stack('scripts')

    @stack('page-scripts')

    @stack('sse-scripts')

</body>

</html>
