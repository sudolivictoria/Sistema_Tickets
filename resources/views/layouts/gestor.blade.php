<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'Help Desk Istu - Gestor')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#84cc16",
                        "secondary": "#04003B",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>

    <style>
        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
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

        .table-container::-webkit-scrollbar {
            height: 6px;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display overflow-hidden">

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
                <h2 class="text-xl font-bold text-secondary tracking-tight">Help Desk Istu <span
                        class="text-green-900 text-xs uppercase ml-2 tracking-widest px-2 py-0.5 bg-primary/10 rounded-full">{{ auth()->user()->unidad->nombre_unidad ?? 'Admin Unidad' }}</span>
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
                    <nav class="flex flex-col gap-1.5 flex-1">
                        <a class="flex items-center gap-3 px-4 py-3 mt-2 {{ request()->routeIs('gestor.dashboard') ? 'bg-primary text-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-xl font-bold mb-4 transition-all"
                            href="{{ route('gestor.dashboard') }}">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span class="text-sm">Dashboard</span>
                        </a>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-2 px-4 font-black">
                            Administración</p>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('gestor.asignar-tickets') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('gestor.asignar-tickets') }}">
                            <span class="material-symbols-outlined text-xl">confirmation_number</span>
                            <span class="text-sm">Asignar Tickets</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('gestor.mis-asignados') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('gestor.mis-asignados') }}">
                            <span class="material-symbols-outlined text-xl">assignment_ind</span>
                            <span class="text-sm">Mis Asignados</span>
                        </a>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-2 px-4 font-black">Servicios
                        </p>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('gestor.crear-ticket') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('gestor.crear-ticket') }}">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="text-sm">Crear Ticket</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('gestor.mis-tickets') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('gestor.mis-tickets') }}">
                            <span class="material-symbols-outlined text-xl">history</span>
                            <span class="text-sm">Mis Tickets</span>
                        </a>

                        <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('gestor.recursos') ? 'bg-primary text-secondary font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg font-bold transition-all"
                            href="{{ route('gestor.recursos') }}">
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
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-8 bg-slate-50 dark:bg-background-dark/50">
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
    <script src="{{ asset('js/auto-refresco.js') }}"></script>

    <script>
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

            setInterval(() => {
                const ahora = new Date();
                let horaFormateada = ahora.toLocaleTimeString('es-SV', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                contenedorReloj.innerText = horaFormateada;
            }, 1000);
        }
        $(document).ready(function() {
            iniciarReloj();
        });
    </script>
</body>
</html>
