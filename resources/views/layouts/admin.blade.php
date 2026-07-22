<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'Help Desk Istu - Admin')</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_istu.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_istu.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    @stack('css')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 8px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
             background: #84cc16;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #04003B;
        }

        .table-container::-webkit-scrollbar {
            height: 6px;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display overflow-hidden">


    <!--Preloader-->
    <div id="preloader"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-white transition-opacity duration-1000">
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-slate-200 border-t-primary"></div>

            <p class="mt-4 text-sm font-black uppercase tracking-widest text-secondary animate-pulse">
                Cargando sistema...
            </p>
        </div>
    </div>

    <div class="flex h-screen flex-col">

        <header
            class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-8 shrink-0 z-20 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-9 bg-primary rounded-lg flex items-center justify-center text-secondary shadow-sm">
                    <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
                </div>
                <h2 class="text-xl font-bold text-secondary tracking-tight">
                    Help Desk Istu
                    <span
                        class="text-green-900 text-xs uppercase ml-2 tracking-widest px-2 py-0.5 bg-primary/10 rounded-full">
                        {{ auth()->user()->unidad->nombre_unidad ?? 'Admin' }}
                    </span>
                </h2>
            </div>
            <div
                class="hidden md:flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                <span class="material-symbols-outlined text-slate-400 text-[18px] animate-pulse">schedule</span>
                <span id="relojSistema" class="text-xs font-black text-slate-600 tracking-wider">00:00:00</span>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">

            <aside class="w-64 bg-secondary flex flex-col shrink-0 z-10 shadow-2xl overflow-y-auto">
                <div class="p-4 flex flex-col h-full">
                    <nav class="flex flex-col space-y-1.5 flex-1">
                        <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-xl font-bold mb-4 transition-all"
                            href="{{ route('admin.dashboard') }}">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span class="text-sm">Dashboard</span>
                        </a>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-2 px-4 font-black">
                            Administración</p>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.asignar-tickets') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.asignar-tickets') }}">
                            <span class="material-symbols-outlined text-xl">confirmation_number</span>
                            <span class="text-sm">Asignar Tickets</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.mis-asignados') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.mis-asignados') }}">
                            <span class="material-symbols-outlined text-xl">assignment_ind</span>
                            <span class="text-sm">Mis Asignados</span>
                        </a>


                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.gestion-usuarios') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.gestion-usuarios') }}">
                            <span class="material-symbols-outlined text-xl">group</span>
                            <span class="text-sm">Gestión Usuarios</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.historial') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.historial') }}">
                            <span class="material-symbols-outlined text-xl">analytics</span>
                            <span class="text-sm">Historial</span>
                        </a>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-2 px-4 font-black">Servicios
                        </p>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.crear-ticket') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.crear-ticket') }}">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="text-sm">Crear Ticket</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.mis-tickets') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('admin.mis-tickets') }}">
                            <span class="material-symbols-outlined text-xl">history</span>
                            <span class="text-sm">Mis Tickets</span>
                        </a>
                    </nav>

                    <div class="mt-auto pt-1 border-t border-white/10">
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
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-8 bg-slate-50 dark:bg-background-dark/50">
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <script>
        //-----------preloader------
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            preloader.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 1000);
        });

        //-----reloj---------- 
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

    @php
        $flashSuccess = session('sweet_success') ?: session('success');
        $flashError = session('sweet_error') ?: session('error');
        $validationErrors = $errors->any() ? $errors->all() : [];
    @endphp

    <script>
        window.__flashMessages = {
            success: @json($flashSuccess),
            error: @json($flashError),
            validationErrors: @json($validationErrors)
        };
    </script>

    @stack('scripts')

    @stack('page-scripts')

    @stack('sse-scripts')
</body>
</html>
