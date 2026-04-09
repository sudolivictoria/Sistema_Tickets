<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Help Desk - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#84cc16",
                        "secondary": "#1e3a8a",
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
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
    <div class="relative flex min-h-screen flex-col">

        <header
            class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-8 bg-primary rounded flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined font-bold">support_agent</span>
                </div>
                <h2 class="text-xl font-bold text-secondary">Help Desk Istu</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-md font-medium text-slate-600">
                    Hola, <span class="text-secondary">{{ auth()->user()->nombre_completo ?? 'Administrador'}}</span>
                </span>
            </div>
        </header>

        <!--sidebar-->
        <div class="flex flex-1 overflow-hidden">
            <aside class="w-64 bg-secondary flex flex-col border-r border-slate-200 shadow-xl">
                <div class="p-6 flex flex-col h-full">
                    <nav class="flex flex-col gap-2 flex-1">
                        <a class="flex items-center gap-3 px-4 py-3 bg-primary text-secondary rounded-lg font-bold shadow-md"
                            href="#">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span class="text-sm">Dashboard</span>
                        </a>

                        <!--cliente-->
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 mt-4 px-4 font-bold">Servicios
                        </p>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="text-sm font-medium">Crear Ticket</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">article</span>
                            <span class="text-sm font-medium">Mis Tickets</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">library_books</span>
                            <span class="text-sm font-medium">Manuales</span>
                        </a>

                    </nav>


                    <!--cerrar sesion-->
                    <div class="mt-auto pt-6 border-t border-white/10">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>

                        <button type="button"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="w-full flex items-center gap-3 px-4 py-3 text-blue-200 hover:text-white hover:bg-red-500/20 rounded-lg transition-all font-bold group">
                            <span
                                class="material-symbols-outlined text-red-400 group-hover:scale-110 transition-transform">logout</span>
                            <span class="text-sm">Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
                
            </main>
        </div>
    </div>
</body>

</html>