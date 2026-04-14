<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
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
    <header
        class="fixed top-0 w-full z-50 bg-white border-b border-slate-100 px-8 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <div class="size-9 bg-secondary rounded-lg flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
            </div>
            <h2 class="text-xl font-bold text-primary tracking-tight">Help Desk Istu</h2>
        </div>
    </header>

    <aside class="fixed left-0 top-0 h-full w-64 z-40 bg-primary border-r border-blue-800 flex flex-col pt-20 p-4">
        <nav class="space-y-1 gap-1-5 flex-1">
            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('cliente.dashboard') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('cliente.dashboard') }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('cliente.crear-ticket') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('cliente.crear-ticket') }}">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                <span class="text-sm">Crear Ticket</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('cliente.mis-tickets') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('cliente.mis-tickets') }}">
                <span class="material-symbols-outlined text-xl">history</span>
                <span class="text-sm">Mis Tickets</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('cliente.recursos') ? 'bg-secondary text-primary' : 'text-slate-300 hover:bg-white/10' }} rounded-xl font-bold transition-all mb-4"
                href="{{ route('cliente.recursos') }}">
                <span class="material-symbols-outlined text-xl">library_books</span>
                <span class="text-sm">Recursos</span>
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:text-white hover:bg-red-500/20 rounded-xl transition-all font-bold group">
                    <span
                        class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">logout</span>
                    <span class="text-sm">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="ml-64 pt-20 min-h-screen">
        <div class="p-8 max-w-[1400px] mx-auto space-y-8">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>